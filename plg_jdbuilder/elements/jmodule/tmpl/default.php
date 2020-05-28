<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);
if ($element->indexMode) {
   return;
}
$element->addClass('jdb-module');
$type = $element->params->get('type', 'module');
$style = $element->params->get('style', '0');
$value = $element->params->get($type, '');

if (empty($value)) {
   return;
}
echo \JDPageBuilder\Builder::renderModule($type, $value, $style);
?>