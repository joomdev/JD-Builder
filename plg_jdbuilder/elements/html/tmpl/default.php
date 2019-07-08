<?php

defined('_JEXEC') or die;
extract($displayData);
$content = $element->params->get('code', '', 'RAW');
if (empty($content)) {
   return;
}
$element->addClass('jdb-html');
echo '<div class="jdb-html-content">' . JDPageBuilder\Helper::renderHTML($content) . '</div>';
?>