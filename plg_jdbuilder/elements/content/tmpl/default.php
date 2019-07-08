<?php

defined('_JEXEC') or die;
extract($displayData);
$content = $element->params->get('content', '', 'RAW');
if (empty($content)) {
   return;
}
$element->addClass('jdb-html');
echo '<div class="jdb-content">' . JDPageBuilder\Helper::renderHTML($content) . '</div>';

$contentStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-content');
$element->addChildStyle($contentStyle);

$typography = $element->params->get('contentTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $contentStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}
?>