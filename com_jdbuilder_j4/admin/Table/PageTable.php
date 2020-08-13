<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomdev\Component\JDBuilder\Administrator\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

/**
 * Pages table
 *
 * @since  1.5
 */
class PageTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__jdbuilder_pages', 'id', $db);
		$this->setColumnAlias('published', 'state');
	}

	public function bind($array, $ignore = '')
	{
		$date = Factory::getDate();
		$task = Factory::getApplication()->input->get('task');



		// Support for multiple field: category_id
		if (isset($array['category_id'])) {
			if (is_array($array['category_id'])) {
				$array['category_id'] = implode(',', $array['category_id']);
			} elseif (strpos($array['category_id'], ',') != false) {
				$array['category_id'] = explode(',', $array['category_id']);
			} elseif (strlen($array['category_id']) == 0) {
				$array['category_id'] = '';
			}
		} else {
			$array['category_id'] = '';
		}

		$input = Factory::getApplication()->input;
		$task = $input->getString('task', '');

		if ($array['id'] == 0 && empty($array['created_by'])) {
			$array['created_by'] = Factory::getUser()->id;
		}

		if ($array['id'] == 0 && empty($array['modified_by'])) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		if ($task == 'apply' || $task == 'save' || $task == 'save2new' || $task == "save2copy") {
			$array['modified_by'] = Factory::getUser()->id;
		}

		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new \JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if ($task == 'apply' || $task == 'save' || $task == 'save2new' || $task == "save2copy") {
			$params = \json_decode($array['params'], true);
			if (!empty($params) && is_array($params)) {
				$array['category_id'] = $params['category_id'];
				$array['language'] = $params['language'];
				$array['state'] = $params['state'];
				$array['access'] = $params['access'];
			}
		}

		if ($task == 'apply' || $task == 'save' || $task == 'save2new') {
			$jdbform = Factory::getApplication()->input->get('_jdbform', [], 'ARRAY');
			$layout = @$jdbform['layout'];
			$db = Factory::getDbo();
			$object = new \stdClass();
			if (!empty($layout)) {
				if (empty($array['layout_id']) || $task == "save2copy") {
					$object->id = NULL;
					$object->layout = $layout;
					$object->created = time();
					$object->updated = time();
					$db->insertObject('#__jdbuilder_layouts', $object);
					$layoutid = $db->insertid();
					$array['layout_id'] = $layoutid;
				} else {
					$object->id = $array['layout_id'];
					$object->layout = $layout;
					$object->updated = time();
					$db->updateObject('#__jdbuilder_layouts', $object, 'id');
				}
			}
		}

		if (!Factory::getUser()->authorise('core.admin', 'com_jdbuilder.page.' . $array['id'])) {
			$actions = \JAccess::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_jdbuilder/access.xml',
				"/access/section[@name='page']/"
			);
			$default_actions = \JAccess::getAssetRules('com_jdbuilder.page.' . $array['id'])->getData();
			$array_jaccess = array();

			foreach ($actions as $action) {
				if (key_exists($action->name, $default_actions)) {
					$array_jaccess[$action->name] = $default_actions[$action->name];
				}
			}

			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}
}
