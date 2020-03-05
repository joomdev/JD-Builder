<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * page Table class
 *
 * @since  1.6
 */
class JdbuilderTablepage extends JTable {

   /**
    * Constructor
    *
    * @param   JDatabase  &$db  A database connector object
    */
   public function __construct(&$db) {
      JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'JdbuilderTablepage', array('typeAlias' => 'com_jdbuilder.page'));
      parent::__construct('#__jdbuilder_pages', 'id', $db);
      $this->setColumnAlias('published', 'state');
   }

   /**
    * Overloaded bind function to pre-process the params.
    *
    * @param   array  $array   Named array
    * @param   mixed  $ignore  Optional array or list of parameters to ignore
    *
    * @return  null|string  null is operation was satisfactory, otherwise returns an error
    *
    * @see     JTable:bind
    * @since   1.5
    */
   public function bind($array, $ignore = '') {
      $date = JFactory::getDate();
      $task = JFactory::getApplication()->input->get('task');



      // Support for multiple field: category_id
      if (isset($array['category_id'])) {
         if (is_array($array['category_id'])) {
            $array['category_id'] = implode(',', $array['category_id']);
         } elseif (strpos($array['category_id'], ',') != false) {
            $array['category_id'] = explode(',', $array['category_id']);
         } elseif (strlen($array['category_id']) == 0) {
            $array['category_id'] = '';
         }
      } else {
         $array['category_id'] = '';
      }

      $input = JFactory::getApplication()->input;
      $task = $input->getString('task', '');

      if ($array['id'] == 0 && empty($array['created_by'])) {
         $array['created_by'] = JFactory::getUser()->id;
      }

      if ($array['id'] == 0 && empty($array['modified_by'])) {
         $array['modified_by'] = JFactory::getUser()->id;
      }

      if ($task == 'apply' || $task == 'save' || $task == 'save2new' || $task == "save2copy") {
         $array['modified_by'] = JFactory::getUser()->id;
      }

      if (isset($array['params']) && is_array($array['params'])) {
         $registry = new JRegistry;
         $registry->loadArray($array['params']);
         $array['params'] = (string) $registry;
      }

      if ($task == 'apply' || $task == 'save' || $task == 'save2new' || $task == "save2copy") {
         $params = \json_decode($array['params'], true);
         if (!empty($params) && is_array($params)) {
            $array['category_id'] = $params['category_id'];
            $array['language'] = $params['language'];
            $array['state'] = $params['state'];
            $array['access'] = $params['access'];
         }
      }

      if ($task == 'apply' || $task == 'save' || $task == 'save2new') {
         $jdbform = \JFactory::getApplication()->input->get('_jdbform', [], 'ARRAY');
         $layout = @$jdbform['layout'];
         $db = JFactory::getDbo();
         $object = new \stdClass();
         if (!empty($layout)) {
            if (empty($array['layout_id']) || $task == "save2copy") {
               $object->id = NULL;
               $object->layout = $layout;
               $object->created = time();
               $object->updated = time();
               $db->insertObject('#__jdbuilder_layouts', $object);
               $layoutid = $db->insertid();
               $array['layout_id'] = $layoutid;
            } else {
               $object->id = $array['layout_id'];
               $object->layout = $layout;
               $object->updated = time();
               $db->updateObject('#__jdbuilder_layouts', $object, 'id');
            }
         }
      }

      if (!JFactory::getUser()->authorise('core.admin', 'com_jdbuilder.page.' . $array['id'])) {
         $actions = JAccess::getActionsFromFile(
                         JPATH_ADMINISTRATOR . '/components/com_jdbuilder/access.xml', "/access/section[@name='page']/"
         );
         $default_actions = JAccess::getAssetRules('com_jdbuilder.page.' . $array['id'])->getData();
         $array_jaccess = array();

         foreach ($actions as $action) {
            if (key_exists($action->name, $default_actions)) {
               $array_jaccess[$action->name] = $default_actions[$action->name];
            }
         }

         $array['rules'] = $this->JAccessRulestoArray($array_jaccess);
      }

      // Bind the rules for ACL where supported.
      if (isset($array['rules']) && is_array($array['rules'])) {
         $this->setRules($array['rules']);
      }

      return parent::bind($array, $ignore);
   }

   /**
    * This function convert an array of JAccessRule objects into an rules array.
    *
    * @param   array  $jaccessrules  An array of JAccessRule objects.
    *
    * @return  array
    */
   private function JAccessRulestoArray($jaccessrules) {
      $rules = array();

      foreach ($jaccessrules as $action => $jaccess) {
         $actions = array();

         if ($jaccess) {
            foreach ($jaccess->getData() as $group => $allow) {
               $actions[$group] = ((bool) $allow);
            }
         }

         $rules[$action] = $actions;
      }

      return $rules;
   }

   /**
    * Overloaded check function
    *
    * @return bool
    */
   public function check() {
      // If there is an ordering column and this is a new row then get the next ordering value
      if (property_exists($this, 'ordering') && $this->id == 0) {
         $this->ordering = self::getNextOrder();
      }



      return parent::check();
   }

   /**
    * Method to set the publishing state for a row or list of rows in the database
    * table.  The method respects checked out rows by other users and will attempt
    * to checkin rows that it can after adjustments are made.
    *
    * @param   mixed    $pks     An optional array of primary key values to update.  If not
    *                            set the instance property value is used.
    * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
    * @param   integer  $userId  The user id of the user performing the operation.
    *
    * @return   boolean  True on success.
    *
    * @since    1.0.4
    *
    * @throws Exception
    */
   public function publish($pks = null, $state = 1, $userId = 0) {
      // Initialise variables.
      $k = $this->_tbl_key;

      // Sanitize input.
      ArrayHelper::toInteger($pks);
      $userId = (int) $userId;
      $state = (int) $state;

      // If there are no primary keys set check to see if the instance key is set.
      if (empty($pks)) {
         if ($this->$k) {
            $pks = array($this->$k);
         }
         // Nothing to set publishing state on, return false.
         else {
            throw new Exception(500, JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
         }
      }

      // Build the WHERE clause for the primary keys.
      $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

      // Determine if there is checkin support for the table.
      if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
         $checkin = '';
      } else {
         $checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
      }

      // Update the publishing state for rows with the given primary keys.
      $this->_db->setQuery(
              'UPDATE `' . $this->_tbl . '`' .
              ' SET `state` = ' . (int) $state .
              ' WHERE (' . $where . ')' .
              $checkin
      );
      $this->_db->execute();

      // If checkin is supported and all rows were adjusted, check them in.
      if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
         // Checkin each row.
         foreach ($pks as $pk) {
            $this->checkin($pk);
         }
      }

      // If the JTable instance value is in the list of primary keys that were set, set the instance.
      if (in_array($this->$k, $pks)) {
         $this->state = $state;
      }

      return true;
   }

   /**
    * Define a namespaced asset name for inclusion in the #__assets table
    *
    * @return string The asset name
    *
    * @see JTable::_getAssetName
    */
   protected function _getAssetName() {
      $k = $this->_tbl_key;

      return 'com_jdbuilder.page.' . (int) $this->$k;
   }

   /**
    * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
    *
    * @param   JTable   $table  Table name
    * @param   integer  $id     Id
    *
    * @see JTable::_getAssetParentId
    *
    * @return mixed The id on success, false on failure.
    */
   protected function _getAssetParentId(JTable $table = null, $id = null) {
      // We will retrieve the parent-asset from the Asset-table
      $assetParent = JTable::getInstance('Asset');

      // Default: if no asset-parent can be found we take the global asset
      $assetParentId = $assetParent->getRootId();

      // The item has the component as asset-parent
      $assetParent->loadByName('com_jdbuilder');

      // Return the found asset-parent-id
      if ($assetParent->id) {
         $assetParentId = $assetParent->id;
      }

      return $assetParentId;
   }

   /**
    * Delete a record by id
    *
    * @param   mixed  $pk  Primary key value to delete. Optional
    *
    * @return bool
    */
   public function delete($pk = null) {
      $this->load($pk);
      $result = parent::delete($pk);

      return $result;
   }

}
