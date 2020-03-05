<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$layout_id = $params->get('jdbuilder_layout', 0);
if (!empty($layout_id)) {
    $layout = \JDPageBuilder\Helper::getLayout($layout_id);
    $layout = new \JDPageBuilder\Element\Layout($layout, 'module', $module->id);
    $rendered = $layout->render();
    \JDPageBuilder\Builder::renderHead();
    echo $rendered;
}
