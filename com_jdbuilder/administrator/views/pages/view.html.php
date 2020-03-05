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
 * View class for a list of Jdbuilder.
 *
 * @since  1.6
 */
class JdbuilderViewPages extends JViewLegacy
{

   protected $items;
   protected $pagination;
   protected $state;

   /**
    * Display the view
    *
    * @param   string  $tpl  Template name
    *
    * @return void
    *
    * @throws Exception
    */
   public function display($tpl = null)
   {
      $this->state = $this->get('State');
      $this->items = $this->get('Items');
      $this->pagination = $this->get('Pagination');
      $this->filterForm = $this->get('FilterForm');
      $this->activeFilters = $this->get('ActiveFilters');

      // Check for errors.
      if (count($errors = $this->get('Errors'))) {
         throw new Exception(implode("\n", $errors));
      }

      JdbuilderHelper::addSubmenu('pages');

      $this->addToolbar();

      $this->sidebar = JHtmlSidebar::render();
      parent::display($tpl);
   }

   /**
    * Add the page title and toolbar.
    *
    * @return void
    *
    * @since    1.6
    */
   protected function addToolbar()
   {
      $state = $this->get('State');
      $canDo = JdbuilderHelper::getActions();

      JToolBarHelper::title(JText::_('COM_JDBUILDER_TITLE_PAGES'), 'stack pages');

      // Check if the form exists before showing the add/edit buttons
      $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/page';

      if (file_exists($formPath)) {
         if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('page.add', 'JTOOLBAR_NEW');
         }

         if ($canDo->get('core.create')) {
            if (isset($this->items[0])) {
               JToolbarHelper::custom('pages.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
            }
         }
      }

      if ($canDo->get('core.edit.state')) {
         if (isset($this->items[0]->state)) {
            JToolBarHelper::divider();
            JToolBarHelper::custom('pages.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::custom('pages.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
         } elseif (isset($this->items[0])) {
            // If this component does not use state then show a direct delete button as we can not trash
            JToolBarHelper::deleteList('', 'pages.delete', 'JTOOLBAR_DELETE');
         }

         if (isset($this->items[0]->state)) {
            //JToolBarHelper::divider();
            //JToolBarHelper::archiveList('pages.archive', 'JTOOLBAR_ARCHIVE');
         }

         if (isset($this->items[0]->checked_out)) {
            JToolBarHelper::custom('pages.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
         }
      }

      // Show trash and delete for components that uses the state field
      if (isset($this->items[0]->state)) {
         if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'pages.delete', 'JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
         } elseif ($canDo->get('core.edit.state')) {
            JToolBarHelper::trash('pages.trash', 'JTOOLBAR_TRASH');
            JToolBarHelper::divider();
         }
      }

      $bar = JToolbar::getInstance('toolbar');
      $bar->appendButton('Custom', '<a target="_blank" class="btn btn-small" href="https://www.youtube.com/playlist?list=PLv9TlpLcSZTAnfiT0x10HO5GGaTJhUB1K"><span class="icon-youtube"></span> ' . JText::_('COM_JDBUILDER_VIDEO_TUTORIALS') . '</a>', 'jdb-tutorials');

      $bar->appendButton('Custom', '<a target="_blank" class="btn btn-small" href="https://docs.joomdev.com/category/jd-builder"><span class="icon-help"></span> ' . JText::_('COM_JDBUILDER_HELP') . '</a>', 'jdb-help');

      if ($canDo->get('core.admin')) {
         JToolBarHelper::preferences('com_jdbuilder');
      }



      // Set sidebar action - New in 3.0
      JHtmlSidebar::setAction('index.php?option=com_jdbuilder&view=pages');
   }

   /**
    * Method to order fields 
    *
    * @return void 
    */
   protected function getSortFields()
   {
      return array(
         'a.`id`' => JText::_('JGRID_HEADING_ID'),
         'a.`title`' => JText::_('COM_JDBUILDER_PAGES_TITLE'),
         'a.`category_id`' => JText::_('COM_JDBUILDER_PAGES_CATEGORY_ID'),
         'a.`ordering`' => JText::_('JGRID_HEADING_ORDERING'),
         'a.`state`' => JText::_('JSTATUS'),
         'a.`access`' => JText::_('COM_JDBUILDER_PAGES_ACCESS'),
         'a.`language`' => JText::_('JGRID_HEADING_LANGUAGE'),
      );
   }

   /**
    * Check if state is set
    *
    * @param   mixed  $state  State
    *
    * @return bool
    */
   public function getState($state)
   {
      return isset($this->state->{$state}) ? $this->state->{$state} : false;
   }
}
