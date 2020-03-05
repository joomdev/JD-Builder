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
   public $accessibility;

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
      $this->state = $this->get('State');
      $this->item = $this->get('Item');
      $this->form = $this->get('Form');
      $this->accessibility = new \stdClass();
      // Check for errors.
      if (count($errors = $this->get('Errors'))) {
         throw new Exception(implode("\n", $errors));
      }
      $this->addToolbar();
      if ($this->item->id && !empty($this->item->params) && is_array($this->item->params)) {
         $this->item->params['state'] = $this->item->state;
         $this->item->params['language'] = $this->item->language;
         $this->item->params['category_id'] = $this->item->category_id;
         $this->item->params['access'] = $this->item->access;
      } else {
         $this->item->id = 0;
         $defaults = JDPageBuilder\Builder::getFormDefaults(JDPageBuilder\Builder::getPageForm());
         $this->item->params = $defaults;
      }
      parent::display($tpl);
   }

   /**
    * Add the page title and toolbar.
    *
    * @return void
    *
    * @throws Exception
    */
   protected function addToolbar() {
      JFactory::getApplication()->input->set('hidemainmenu', true);

      $user = JFactory::getUser();
      $isNew = ($this->item->id == 0);

      if (isset($this->item->checked_out)) {
         $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
      } else {
         $checkedOut = false;
      }

      $canDo = JdbuilderHelper::getActions();

      JToolbarHelper::title(
              JText::_('COM_JDBUILDER_TITLE_PAGE_' . ($checkedOut ? 'VIEW_PAGES' : ($isNew ? 'ADD_PAGES' : 'EDIT_PAGES'))), 'pencil-2 article-add'
      );

      $this->accessibility->canSave = false;
      $this->accessibility->canApply = false;
      $this->accessibility->canSaveNew = false;
      $this->accessibility->canSaveCopy = false;

      // If not checked out, can save the item.
      if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {
         $this->accessibility->canSave = true;
         $this->accessibility->canApply = true;
      }

      if (!$checkedOut && ($canDo->get('core.create'))) {
         $this->accessibility->canSaveNew = true;
      }

      // If an existing item, can save to a copy.
      if (!$isNew && $canDo->get('core.create')) {
         $this->accessibility->canSaveCopy = true;
      }
   }

}
