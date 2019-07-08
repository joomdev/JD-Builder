<?php

defined('_JEXEC') or die;
extract($displayData);
$element->addClass('jdb-module');
$type = $element->params->get('type', 'module');
$style = $element->params->get('style', '0');
$value = $element->params->get($type, '');

if (empty($value)) {
   return;
}
echo \JDPageBuilder\Builder::renderModule($type, $value, $style);
?>