<?php

defined('_JEXEC') or die;
extract($displayData);


$space = $element->params->get('space', null);
if (!empty($space)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($space->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($space->{$deviceKey})) {
         $element->addCss("height", $space->{$deviceKey}->value . 'px', $device);
      }
   }
} else {
   $element->addCss("height", '50px');
}
?>