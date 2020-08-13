<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomdev\Component\JDBuilder\Administrator\View\Page;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit an article.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

	public $accessibility;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->accessibility = new \stdClass();

		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		if ($this->item->id && !empty($this->item->params) && is_array($this->item->params)) {
			$this->item->params['state'] = $this->item->state;
			$this->item->params['language'] = $this->item->language;
			$this->item->params['category_id'] = $this->item->category_id;
			$this->item->params['access'] = $this->item->access;
		} else {
			$this->item->id = 0;
			$defaults = \JDPageBuilder\Builder::getFormDefaults(\JDPageBuilder\Builder::getPageForm());
			$this->item->params = $defaults;
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$isNew      = ($this->item->id == 0);
		$user = Factory::getUser();

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} else {
			$checkedOut = false;
		}

		$canDo = ContentHelper::getActions('com_jdbuilder');

		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title(
			Text::_('COM_JDBUILDER_TITLE_PAGE_' . ($checkedOut ? 'VIEW_PAGES' : ($isNew ? 'ADD_PAGES' : 'EDIT_PAGES'))),
			'pencil-2 article-add'
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
