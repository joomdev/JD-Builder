<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * Jdbuilder helper.
 *
 * @since  1.6
 */
class JdbuilderHelper
{

   /**
    * Configure the Linkbar.
    *
    * @param   string  $vName  string
    *
    * @return void
    */
   public static function addSubmenu($vName = '')
   {
      JHtmlSidebar::addEntry(
         JText::_('COM_JDBUILDER_TITLE_PAGES'),
         'index.php?option=com_jdbuilder&view=pages',
         $vName == 'pages'
      );

      JHtmlSidebar::addEntry(
         JText::_('JCATEGORIES'),
         "index.php?option=com_categories&extension=com_jdbuilder",
         $vName == 'categories'
      );

      if ($vName == 'categories') {
         JToolBarHelper::title('JD Builder: JCATEGORIES');
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
   public static function getFiles($pk, $table, $field)
   {
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
   public static function getActions()
   {
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

   public static function versionMessage()
   {
      $buiderConfig = JComponentHelper::getParams('com_jdbuilder');
      $key = $buiderConfig->get('key',  '');
      $isPro = file_exists(JPATH_PLUGINS . '/system/jdbuilder/options/default-pro.xml');
    
      if ($isPro && !empty($key)) {
         return;
      }
      $document = JFactory::getDocument();
      $document->addStyleDeclaration('.jdb-pro-message{background-image:linear-gradient(110deg, #099a97 6%, #15cda8 100%);box-shadow:8px 8px 8px rgba(221, 221, 221, 0.81);overflow:hidden;border-radius:4px;display:flex;color:#fff;margin-bottom:25px;line-height: 1.5rem;font-size:14px;}.jdb-pro-message h4{font-size:16px;margin:0 0 10px;}body.admin.com_jdbuilder #content .jdb-pro-message a{color:#fff;}.jdb-pro-logo{color: white;width: 100px;display: grid;align-items: center;padding: 15px;box-sizing: border-box;background-image:linear-gradient(110.7deg, #099a97 6.3%, #15cda8 90.6%);margin-right:10px;}.jdb-content{padding:20px 10px;align-self:center;}.jdb-content p{color:#f1f1f1;margin:0;}.jdb-content strong{color:#fff;}');

      $prefix = $isPro ? 'PRO' : 'FREE';
      return '<div class="jdb-pro-message"><div class="jdb-pro-logo">' . file_get_contents(JURI::root() . 'media/jdbuilder/images/jdb-icon.svg') . '</div>
         <div class="jdb-content">
         <h4>' . JText::_('COM_JDBUILDER_VER_MSG_' . $prefix . '_TITLE') . '</h4>
         <p>' . JText::_('COM_JDBUILDER_VER_MSG_' . $prefix . '_DESC') . '</p></div></div>';
   }
}
