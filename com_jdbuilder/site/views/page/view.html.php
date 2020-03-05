<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.6
 */
class JdbuilderViewPage extends JViewLegacy {

   protected $state;
   protected $item;
   protected $form;
   protected $params;

   /**
    * Display the view
    *
    * @param   string  $tpl  Template name
    *
    * @return void
    *
    * @throws Exception
    */
   public function display($tpl = null) {
      $app = JFactory::getApplication();
      $user = JFactory::getUser();

      $this->state = $this->get('State');
      $this->item = $this->get('Item');
      $this->params = $app->getParams('com_jdbuilder');

      if (!empty($this->item)) {
         
      }

      // Check for errors.
      if (count($errors = $this->get('Errors'))) {
         throw new Exception(implode("\n", $errors));
      }

      if(!JDB_LIVE_PREVIEW){
         if (!in_array($this->item->access, $user->getAuthorisedViewLevels())) {
            return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
         }
      }


      if ($this->_layout == 'edit') {
         $authorised = $user->authorise('core.create', 'com_jdbuilder');

         if ($authorised !== true) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
         }
      }

      $this->_prepareDocument();

      parent::display($tpl);
   }

   /**
    * Prepares the document
    *
    * @return void
    *
    * @throws Exception
    */
   protected function _prepareDocument() {
      $app = JFactory::getApplication();
      $menus = $app->getMenu();
      $title = null;

      // Because the application sets a default page title,
      // We need to get it from the menu item itself
      $menu = $menus->getActive();

      if ($menu) {
         $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
      } else {
         $this->params->def('page_heading', JText::_('COM_JDBUILDER_DEFAULT_PAGE_TITLE'));
      }

      $title = $this->params->get('page_title', '');

      if (empty($title)) {
         $title = $app->get('sitename');
      } elseif ($app->get('sitename_pagetitles', 0) == 1) {
         $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
      } elseif ($app->get('sitename_pagetitles', 0) == 2) {
         $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
      }

      $this->document->setTitle($title);

      if ($this->params->get('menu-meta_description')) {
         $this->document->setDescription($this->params->get('menu-meta_description'));
      }

      if ($this->params->get('menu-meta_keywords')) {
         $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
      }

      if ($this->params->get('robots')) {
         $this->document->setMetadata('robots', $this->params->get('robots'));
      }
   }

}
