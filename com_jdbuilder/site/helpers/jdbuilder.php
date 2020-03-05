<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('JdbuilderHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jdbuilder' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jdbuilder.php');

/**
 * Class JdbuilderFrontendHelper
 *
 * @since  1.6
 */
class JdbuilderHelpersJdbuilder {

   /**
    * Get category name using category ID
    * @param integer $category_id Category ID
    * @return mixed category name if the category was found, null otherwise
    */
   public static function getCategoryNameByCategoryId($category_id) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);

      $query
              ->select('title')
              ->from('#__categories')
              ->where('id = ' . intval($category_id));

      $db->setQuery($query);
      return $db->loadResult();
   }

   /**
    * Get an instance of the named model
    *
    * @param   string  $name  Model name
    *
    * @return null|object
    */
   public static function getModel($name) {
      $model = null;

      // If the file exists, let's
      if (file_exists(JPATH_SITE . '/components/com_jdbuilder/models/' . strtolower($name) . '.php')) {
         require_once JPATH_SITE . '/components/com_jdbuilder/models/' . strtolower($name) . '.php';
         $model = JModelLegacy::getInstance($name, 'JdbuilderModel');
      }

      return $model;
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
    * Gets the edit permission for an user
    *
    * @param   mixed  $item  The item
    *
    * @return  bool
    */
   public static function canUserEdit($item) {
      $permission = false;
      $user = JFactory::getUser();

      if ($user->authorise('core.edit', 'com_jdbuilder')) {
         $permission = true;
      } else {
         if (isset($item->created_by)) {
            if ($user->authorise('core.edit.own', 'com_jdbuilder') && $item->created_by == $user->id) {
               $permission = true;
            }
         } else {
            $permission = true;
         }
      }

      return $permission;
   }

}
