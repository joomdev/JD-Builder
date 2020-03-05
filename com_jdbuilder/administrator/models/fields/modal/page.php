<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

 // No direct access
 defined('_JEXEC') or die('Restricted access');
 
 jimport('joomla.form.formfield');
 
 /**
  * Book form field class
  */
 class JFormFieldModal_Page extends JFormField
 {
 	/**
	 * Method to get the html for the input field.
	 *
	 * @return  string  The field input html.
	 */
	protected function getInput()
	{
		// Load language
		//JFactory::getLanguage()->load('com_jdbuilder', JPATH_ADMINISTRATOR);

		// $this->value is set if there's a default id specified in the xml file
		$value = (int) $this->value > 0 ? (int) $this->value : '';
        
		// $this->id will be jform_request_xxx where xxx is the name of the field in the xml file
		$modalId = 'Page_' . $this->id;

		// Add the modal field script to the document head.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// our callback function from the modal to the main window:
		JFactory::getDocument()->addScriptDeclaration("
			function jSelectPage_" . $this->id . "(id, title) {
				window.processModalSelect('Page', '" . $this->id . "', id, title);
			}
			");

			// Setup variables for display.
		// Setup variables for display.
		$linkPages = 'index.php?option=com_jdbuilder&amp;view=pages&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$linkPage  = 'index.php?option=com_jdbuilder&amp;view=page&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$modalTitle   = JText::_('COM_CONTACT_CHANGE_CONTACT');

		if (isset($this->element['language']))
		{
			$linkPages .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkPage  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle   .= ' &#8212; ' . $this->element['label'];
		}

		$urlSelect = $linkPages . '&amp;function=jSelectPage_' . $this->id;
		$urlEdit   = 'index.php?option=com_jdbuilder&amp;view=page&amp;task=page.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
		$urlNew    = 'index.php?option=com_jdbuilder&amp;view=page&amp;task=page.add';
		
		// if a default id is set, then get the corresponding greeting to display it
		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__jdbuilder_pages'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}
		
        
		// display the default greeting or "Select" if no default specified
		$title = empty($title) ? JText::_('COM_JDBUILDER_MENUITEM_SELECT_PAGE') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// html for the Select button
		$html .= '<a'
			. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
			. ' id="' . $this->id . '_select"'
			. ' data-toggle="modal"'
			. ' role="button"'
			. ' href="#ModalSelect' . $modalId . '"'
			. ' title="' . JHtml::tooltipText('COM_JDBUILDER_MENUITEM_SELECT_PAGE') . '">'
			. '<span class="icon-file" aria-hidden="true"></span> ' . JText::_('JSELECT')
			. '</a>';

		// New Page button
		$html .= '<button'
			. ' type="button"'
			. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
			. ' id="' . $this->id . '_new"'
			. ' data-toggle="modal"'
			. ' data-target="#ModalNew' . $modalId . '"'
			. ' title="' . JHtml::tooltipText('COM_JDBUILDER_NEW_PAGE') . '">'
			. '<span class="icon-new" aria-hidden="true"></span> ' . JText::_('JACTION_CREATE')
			. '</button>';

		// Edit Page button
		$html .= '<button'
		. ' type="button"'
		. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
		. ' id="' . $this->id . '_edit"'
		. ' data-toggle="modal"'
		. ' data-target="#ModalEdit' . $modalId . '"'
		. ' title="' . JHtml::tooltipText('COM_JDBUILDER_EDIT_PAGE') . '">'
		. '<span class="icon-edit" aria-hidden="true"></span> ' . JText::_('JACTION_EDIT')
		. '</button>';
		// html for the Clear button
		$html .= '<a'
			. ' class="btn' . ($value ? '' : ' hidden') . '"'
			. ' id="' . $this->id . '_clear"'
			. ' href="#"'
			. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
			. '<span class="icon-remove" aria-hidden="true"></span>' . JText::_('JCLEAR')
			. '</a>';

		$html .= '</span>';



		// title to go in the modal header
		$modalTitle    = JText::_('COM_JDBUILDER_MENUITEM_SELECT_PAGE');
        
		// html to set up the modal iframe
		$html .= JHtml::_(
			'bootstrap.renderModal',
			'ModalSelect' . $modalId,
			array(
				'title'       => $modalTitle,
				'url'         => $urlSelect,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
			)
		);

		$html .= JHtml::_(
			'bootstrap.renderModal',
			'ModalNew' . $modalId,
			array(
				'title'       => JText::_('COM_JDBUILDER_NEW_PAGE'),
				'backdrop'    => 'static',
				'keyboard'    => false,
				'closeButton' => false,
				'url'         => $urlNew,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<button type="button" class="btn"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'page\', \'cancel\', \'item-form\'); return false;">'
						. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
						// . '<button type="button" class="btn btn-primary"'
						// . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'page\', \'save\', \'item-form\'); return false;">'
						// . JText::_('JSAVE') . '</button>'
						// . '<button type="button" class="btn btn-success"'
						// . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'page\', \'apply\', \'item-form\'); return false;">'
						// . JText::_('JAPPLY') . '</button>',
			)
		);

		$html .= JHtml::_(
			'bootstrap.renderModal',
			'ModalEdit' . $modalId,
			array(
				'title'       => JText::_('COM_JDBUILDER_EDIT_PAGE'),
				'backdrop'    => 'static',
				'keyboard'    => false,
				'closeButton' => false,
				'url'         => $urlEdit,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<button type="button" class="btn"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'page\', \'cancel\', \'item-form\'); return false;">'
						. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
						// . '<button type="button" class="btn btn-primary"'
						// . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'page\', \'save\', \'item-form\'); return false;">'
						// . JText::_('JSAVE') . '</button>'
						// . '<button type="button" class="btn btn-success"'
						// . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'page\', \'apply\', \'item-form\'); return false;">'
						// . JText::_('JAPPLY') . '</button>',
			)
		);

		// class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		// hidden input field to store the Page record id
		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class 
			. ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(JText::_('COM_JDBUILDER_MENUITEM_SELECT_PAGE', true), ENT_COMPAT, 'UTF-8') 
			. '" value="' . $value . '" />';

		return $html;
	}

	/**
	 * Method to get the html for the label field.
	 *
	 * @return  string  The field label html.
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}

 	
 }

?>