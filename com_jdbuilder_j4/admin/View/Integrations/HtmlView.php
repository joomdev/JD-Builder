<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomdev\Component\JDBuilder\Administrator\View\Integrations;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions('com_jdbuilder');

		ToolBarHelper::title(Text::_('COM_JDBUILDER_TITLE_INTEGRATIONS'), 'stack pages');


		$bar = Toolbar::getInstance('toolbar');
		$bar->appendButton('Custom', '<a target="_blank" class="btn btn-noextenal-icon btn-small mr-3" href="https://www.youtube.com/playlist?list=PLv9TlpLcSZTAnfiT0x10HO5GGaTJhUB1K"><span class="icon-youtube"></span> ' . Text::_('COM_JDBUILDER_VIDEO_TUTORIALS') . '</a>', 'jdb-tutorials');

		$bar->appendButton('Custom', '<a target="_blank" class="btn btn-noextenal-icon btn-small" href="https://docs.joomdev.com/category/jd-builder"><span class="icon-help"></span> ' . Text::_('COM_JDBUILDER_HELP') . '</a>', 'jdb-help');

		if ($canDo->get('core.admin')) {
			ToolBarHelper::preferences('com_jdbuilder');
		}
	}
}
