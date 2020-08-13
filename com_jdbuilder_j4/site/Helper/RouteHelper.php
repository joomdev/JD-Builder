<?php

/**
 * @package     Jdbuilder.Site
 * @subpackage  com_builder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomdev\Component\JDBuilder\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;

/**
 * Mywalks Component Route Helper.
 *
 * @since  1.5
 */
abstract class RouteHelper
{
	/**
	 * Get the article route.
	 *
	 * @param   integer  $id        The route of the content item.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The article route.
	 *
	 * @since   1.5
	 */
	public static function getPageRoute($id, $slug, $language = 0, $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_jdbuilder&view=page&id=' . $id . '&slug=' . $slug;

		if ($language && $language !== '*' && Multilanguage::isEnabled()) {
			$link .= '&lang=' . $language;
		}

		if ($layout) {
			$link .= '&layout=' . $layout;
		}

		return $link;
	}
}
