<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * JDBuilder search plugins
 *
 * @since 1.0.4
 */
class PlgSearchJDBuilder extends JPlugin
{

	protected $autoloadLanguage = true;

	protected $app;

	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 */

	public function onContentSearchAreas()
	{
		static $areas = array(
			'jdbuilder' => 'PLG_SEARCH_JDBUILDER_AREAS'
		);

		return $areas;
	}

	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db 	= JFactory::getDbo();
		$limit 	= $this->params->def('search_limit', 50);
		$tag    = JFactory::getLanguage()->getTag();

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		$text = trim($text);

		if ($text == '') {
			return array();
		}

		JLoader::register('JdbuilderRouter', JPATH_SITE . '/components/com_jdbuilder/router.php');

		switch ($phrase) {
			case 'exact':
			case 'all':
			case 'any':
			default:
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres1 = array();
				$wheres1[] = 'p.title LIKE ' . $text;
				$wheres1[] = 'l.layout LIKE ' . $text;
				$where = '((' . implode(') OR (', $wheres1) . ')) AND p.state = 1';
				break;
		}

		switch ($ordering) {
			case 'oldest':
				$order = 'p.id ASC';
				break;

			case 'alpha':
				$order = 'p.title ASC';
				break;

			case 'newest':
			case 'category':
			case 'popular':
			default:
				$order = 'p.id DESC';
				break;
		}

		$query = $db->getQuery(true);
		$section = JText::_('PLG_SEARCH_JDBUILDER_PAGES');

		if ($limit > 0) {
			$query->clear();
			$query->select('p.id as id, p.title as title, p.language as language, FROM_UNIXTIME(l.created) as created, ' . $query->concatenate(array($db->quote($section), 'p.title'), ' / ') . ' as section, '
				. '\'2\' AS browsernav');
			$query->from($db->quoteName('#__jdbuilder_pages', 'p'));
			$query->join('LEFT', '#__jdbuilder_layouts AS l ON l.id = p.layout_id');
			// $query->where($db->quoteName('s.extension') . ' = '  . $db->quote('com_jdbuilder'));
			$query->where($where);

			if ($this->app->isClient('site') && JLanguageMultilang::isEnabled()) {
				$query->where('p.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
			}

			$query->order($order);

			$db->setQuery($query, 0, $limit);
		}

		try {
			$list = $db->loadObjectList();

			if (isset($list)) {
				foreach ($list as $key => $item) {
					$menuItem = $this->getActiveMenu($item->id);
					$itemId = '';
					if (isset($menuItem->id) && $menuItem->id) {
						$itemId = '&Itemid=' . $menuItem->id;
					}
					$list[$key]->href = JRoute::_('index.php?option=com_jdbuilder&view=page&id=' . $item->id . ((($item->language != '*')) ? '&lang=' . $item->language : '') . $itemId);
				}
			}
		} catch (RuntimeException $e) {
			echo $e->getMessage();
			echo $e->getLine();
			$list = array();
			$this->app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $list;
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
