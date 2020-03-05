<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Content Component Association Helper.
 *
 * @since  3.0
 */
class JdbuilderHelperAssociation extends CategoryHelperAssociation {

   /**
    * Method to get the associations for a given item
    *
    * @param   integer  $id    Id of the item
    * @param   string   $view  Name of the view
    *
    * @return  array   Array of associations for the item
    *
    * @since  3.0
    */
   public static function getAssociations($id = 0, $view = null) {
      jimport('helper.route', JPATH_COMPONENT_SITE);

      $app = JFactory::getApplication();
      $jinput = $app->input;
      $view = is_null($view) ? $jinput->get('view') : $view;
      $id = empty($id) ? $jinput->getInt('id') : $id;

      if ($view == 'category' || $view == 'categories') {
         return self::getCategoryAssociations($id, 'com_jdbuilder');
      }

      return array();
   }

}
