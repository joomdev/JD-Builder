<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use JDPageBuilder\Builder;
use JDPageBuilder\Element\Layout;

JLoader::register('FinderIndexerAdapter', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

class PlgFinderJDBuilder extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 */
	protected $context = 'JDBuilder';

	/**
	 * The extension name.
	 */
	protected $extension = 'com_jdbuilder';

	/**
	 * The sublayout to use when rendering the results.
	 */
	protected $layout = 'page';

	/**
	 * The type of content that the adapter indexes.
	 */
	protected $type_title = 'Page';

	/**
	 * The table name.
	 */
	protected $table = '#__jdbuilder_page';

	/**
	 * The field the published state is stored in.
	 */
	protected $state_field = 'published';

	/**
	 * Load the language file on instantiation.
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method to remove the link information for items that have been deleted.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context === 'com_jdbuilder.page') {
			$id = $table->id;
		} elseif ($context === 'com_finder.index') {
			$id = $table->link_id;
		} else {
			return true;
		}

		// Remove the items.
		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		if ($context === 'com_jdbuilder.page') {
			if (!$isNew && $this->old_access != $row->access) {
				$this->itemAccessChange($row);
			}

			$this->reindex($row->id);
		}

		return true;
	}

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		if ($context === 'com_jdbuilder.page') {
			if (!$isNew) {
				$this->checkItemAccess($row);
			}
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		if ($context === 'com_jdbuilder.page') {
			$this->itemStateChange($pks, $value);
		}

		if ($context === 'com_plugins.plugin' && $value === 0) {
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		Builder::init(JFactory::getApplication());
		// Check if the extension is enabled
		if (JComponentHelper::isEnabled($this->extension) === false) {
			return;
		}

		$menuItem = self::getActiveMenu($item->id);
		$item->setLanguage();
		$item->url = $this->getUrl($item->id, $this->extension, $this->layout);

		$object = $item;
		$object->layout = $item->body;
		$layout = new Layout($object, 'page', $item->id, true);
		$rendered = $layout->render();

		$item->body = trim(strip_tags($rendered));

		$link = 'index.php?option=com_jdbuilder&view=page&id=' . $item->id;

		if ($item->language && $item->language !== '*' && JLanguageMultilang::isEnabled()) {
			$link .= '&lang=' . $item->language;
		}

		if (isset($menuItem->id) && $menuItem->id) {
			$link .= '&Itemid=' . $menuItem->id;
		}

		$item->route = $link;

		$item->path = $item->route;

		if (isset($menuItem->title) && $menuItem->title) {
			$item->title = $menuItem->title;
		}

		// Handle the page author data.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'user');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Page');

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 */
	protected function setup()
	{
		JLoader::register('JdbuilderRouter', JPATH_SITE . '/components/com_jdbuilder/router.php');

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of page items.
	 */
	protected function getListQuery($query = null)
	{
		$db = JFactory::getDbo();

		// Check if we can use the supplied SQL query.
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.id, a.title AS title, l.layout as body, FROM_UNIXTIME(l.created) as start_date')
			->select('a.created_by, FROM_UNIXTIME(l.updated) as modified, a.modified_by, a.language')
			->select('a.access, a.category_id, a.state, a.ordering')

			->select('u.name')
			->from('#__jdbuilder_pages AS a')
			->join('LEFT', '#__users AS u ON u.id = a.created_by')
			->join('LEFT', '#__jdbuilder_layouts AS l ON l.id = a.layout_id');

		return $query;
	}

	public static function getActiveMenu($pageId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('title, id'));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_jdbuilder&view=page&id=' . $pageId . '%'));
		$query->where($db->quoteName('published') . ' = ' . $db->quote('1'));
		$db->setQuery($query);
		$item = $db->loadObject();

		return $item;
	}
}
