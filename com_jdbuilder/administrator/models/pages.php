<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Jdbuilder records.
 *
 * @since  1.6
 */
class JdbuilderModelPages extends JModelList {

   /**
    * Constructor.
    *
    * @param   array  $config  An optional associative array of configuration settings.
    *
    * @see        JController
    * @since      1.6
    */
   public function __construct($config = array()) {
      if (empty($config['filter_fields'])) {
         $config['filter_fields'] = array(
             'id', 'a.`id`',
             'title', 'a.`title`',
             'category_id', 'a.`category_id`',
             'ordering', 'a.`ordering`',
             'state', 'a.`state`',
             'access', 'a.`access`',
             'language', 'a.`language`',
             'created_by', 'a.`created_by`',
             'modified_by', 'a.`modified_by`',
         );
      }

      parent::__construct($config);
   }

   /**
    * Method to auto-populate the model state.
    *
    * Note. Calling getState in this method will result in recursion.
    *
    * @param   string  $ordering   Elements order
    * @param   string  $direction  Order direction
    *
    * @return void
    *
    * @throws Exception
    */
   protected function populateState($ordering = null, $direction = null) {
      // List state information.
      parent::populateState('id', 'ASC');

      $context = $this->getUserStateFromRequest($this->context . '.context', 'context', 'com_content.article', 'CMD');
      $this->setState('filter.context', $context);

      $this->setState('filter.component', 'com_content');
      $this->setState('filter.section', 'article');
   }

   /**
    * Method to get a store id based on model configuration state.
    *
    * This is necessary because the model is used by the component and
    * different modules that might need different sets of data or different
    * ordering requirements.
    *
    * @param   string  $id  A prefix for the store id.
    *
    * @return   string A store id.
    *
    * @since    1.6
    */
   protected function getStoreId($id = '') {
      // Compile the store id.
      $id .= ':' . $this->getState('filter.search');
      $id .= ':' . $this->getState('filter.state');


      return parent::getStoreId($id);
   }

   /**
    * Build an SQL query to load the list data.
    *
    * @return   JDatabaseQuery
    *
    * @since    1.6
    */
   protected function getListQuery() {
      // Create a new query object.
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      // Select the required fields from the table.
      $query->select(
              $this->getState(
                      'list.select', 'DISTINCT a.*'
              )
      );
      $query->from('`#__jdbuilder_pages` AS a');

      // Join over the users for the checked out user
      $query->select("uc.name AS uEditor");
      $query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

      // Join over the access level field 'access'
      $query->select('`access`.title AS `access`');
      $query->join('LEFT', '#__viewlevels AS access ON `access`.id = a.`access`');

      // Join over the user field 'created_by'
      $query->select('`created_by`.name AS `created_by`');
      $query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

      // Join over the user field 'modified_by'
      $query->select('`modified_by`.name AS `modified_by`');
      $query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');


      // Filter by published state
      $published = $this->getState('filter.state');
      if (is_numeric($published)) {
         $query->where('a.state = ' . (int) $published);
      } elseif ($published === '') {
         $query->where('(a.state IN (0, 1))');
      } elseif ($published === null) {
         $query->where('(a.state IN (0, 1))');
      }

      // Filter by search in title
      $search = $this->getState('filter.search');

      if (!empty($search)) {
         if (stripos($search, 'id:') === 0) {
            $query->where('a.id = ' . (int) substr($search, 3));
         } else {
            $search = $db->Quote('%' . $db->escape($search, true) . '%');
            $query->where('( a.title LIKE ' . $search . '  OR  a.category_id LIKE ' . $search . ' )');
         }
      }



      // Filtering category_id
      $filter_category_id = $this->state->get("filter.category_id");

      if (is_numeric($filter_category_id)) {
         $query->where('a.category_id = ' . (int) $filter_category_id);
      } elseif (is_array($filter_category_id)) {
         $category_id = ArrayHelper::toInteger($filter_category_id);
         $category_id = implode(',', $category_id);
         $query->where('a.category_id IN (' . $category_id . ')');
      }

      // Filter by access level.
      $access = $this->getState('filter.access');

      if (is_numeric($access)) {
         $query->where('a.access = ' . (int) $access);
      } elseif (is_array($access)) {
         $access = ArrayHelper::toInteger($access);
         $access = implode(',', $access);
         $query->where('a.access IN (' . $access . ')');
      }
      /* if(!empty($access)){
         $access = implode('|', $access);
         $query->where('CONCAT(",", `access`, ",") REGEXP ",(' . $access . '),"');
      } */

      // Filtering language
      $filter_language = $this->state->get("filter.language");

      if ($filter_language !== null && !empty($filter_language)) {
         $query->where("a.`language` = '" . $db->escape($filter_language) . "'");
      }
      // Add the list ordering clause.
      $orderCol = $this->state->get('list.ordering', 'id');
      $orderDirn = $this->state->get('list.direction', 'ASC');

      if ($orderCol && $orderDirn) {
         $query->order($db->escape($orderCol . ' ' . $orderDirn));
      }

      return $query;
   }

   /**
    * Get an array of data items
    *
    * @return mixed Array of data items on success, false on failure.
    */
   public function getItems() {
      $items = parent::getItems();

      foreach ($items as $oneItem) {

         if (isset($oneItem->category_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__categories'))
                    ->where('FIND_IN_SET(' . $db->quoteName('id') . ', ' . $db->quote($oneItem->category_id) . ')');

            $db->setQuery($query);
            $result = $db->loadColumn();

            $oneItem->category_id = !empty($result) ? implode(', ', $result) : '';
         }
         
         /* if (isset($oneItem->access)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__viewlevels'))
                    ->where('FIND_IN_SET(' . $db->quoteName('id') . ', ' . $db->quote($oneItem->access) . ')');

            $db->setQuery($query);
            $result = $db->loadColumn();

            $oneItem->access = !empty($result) ? implode(', ', $result) : '';
         } */
      }

      return $items;
   }

}
