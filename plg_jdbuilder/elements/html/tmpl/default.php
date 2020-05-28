<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);
$content = $element->params->get('code', '', 'RAW');
if (empty($content)) {
   return;
}
$element->addClass('jdb-html');
echo '<div class="jdb-html-content">' . $content . '</div>';
