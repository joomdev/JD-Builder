<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Jdbuilder helper.
 *
 * @since  1.6
 */
class JdbuilderHelper {

   /**
    * Configure the Linkbar.
    *
    * @param   string  $vName  string
    *
    * @return void
    */
   public static function addSubmenu($vName = '') {
      JHtmlSidebar::addEntry(
              JText::_('COM_JDBUILDER_TITLE_PAGES'), 'index.php?option=com_jdbuilder&view=pages', $vName == 'pages'
      );

      JHtmlSidebar::addEntry(
              JText::_('JCATEGORIES') . ' (' . JText::_('COM_JDBUILDER_TITLE_PAGES') . ')', "index.php?option=com_categories&extension=com_jdbuilder.pages", $vName == 'categories.pages'
      );
      if ($vName == 'categories') {
         JToolBarHelper::title('JD Builder: JCATEGORIES (COM_JDBUILDER_TITLE_PAGES)');
      }
   }

   /**
    * Gets the files attached to an item
    *
    * @param   int     $pk     The item's id
    *
    * @param   string  $table  The table's name
    *
    * @param   string  $field  The field's name
    *
    * @return  array  The files
    */
   public static function getFiles($pk, $table, $field) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);

      $query
              ->select($field)
              ->from($table)
              ->where('id = ' . (int) $pk);

      $db->setQuery($query);

      return explode(',', $db->loadResult());
   }

   /**
    * Gets a list of the actions that can be performed.
    *
    * @return    JObject
    *
    * @since    1.6
    */
   public static function getActions() {
      $user = JFactory::getUser();
      $result = new JObject;

      $assetName = 'com_jdbuilder';

      $actions = array(
          'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
      );

      foreach ($actions as $action) {
         $result->set($action, $user->authorise($action, $assetName));
      }

      return $result;
   }

}
