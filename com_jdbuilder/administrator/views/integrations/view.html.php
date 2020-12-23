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
class JdbuilderViewIntegrations extends JViewLegacy
{

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
      JdbuilderHelper::addSubmenu('integrations');

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
      $canDo = JdbuilderHelper::getActions();

      JToolBarHelper::title(JText::_('COM_JDBUILDER_TITLE_INTEGRATIONS'), 'stack pages');


      $bar = JToolbar::getInstance('toolbar');
      $bar->appendButton('Custom', '<a target="_blank" class="btn btn-small" href="https://www.youtube.com/playlist?list=PLv9TlpLcSZTAnfiT0x10HO5GGaTJhUB1K"><span class="icon-youtube"></span> ' . JText::_('COM_JDBUILDER_VIDEO_TUTORIALS') . '</a>', 'jdb-tutorials');

      $bar->appendButton('Custom', '<a target="_blank" class="btn btn-small" href="https://docs.joomdev.com/category/jd-builder"><span class="icon-help"></span> ' . JText::_('COM_JDBUILDER_HELP') . '</a>', 'jdb-help');

      if ($canDo->get('core.admin')) {
         JToolBarHelper::preferences('com_jdbuilder');
      }

      // Set sidebar action - New in 3.0
      JHtmlSidebar::setAction('index.php?option=com_jdbuilder&view=pages');
   }
}
