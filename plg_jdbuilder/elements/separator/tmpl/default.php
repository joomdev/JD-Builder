<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);
$element->addClass('jdb-divider');
?>
<div class="jdb-divider-main"></div>
<?php
$style = new JDPageBuilder\Element\ElementStyle('.jdb-divider-main');
$element->addChildStyle($style);

$style->addCss("border-top-style", $element->params->get('separatorType', 'solid'));
$style->addCss("border-top-color", $element->params->get('separatorColor', ''));

$weight = $element->params->get('separatorWeight', null);
if (!empty($weight)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($weight->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($weight->{$deviceKey})) {
         $style->addCss("border-top-width", $weight->{$deviceKey}->value . 'px', $device);
      }
   }
}

$width = $element->params->get('separatorWidth', null);
if (!empty($width)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($width->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
         $style->addCss("width", $width->{$deviceKey}->value . $width->{$deviceKey}->unit, $device);
      }
   }
}

$gap = $element->params->get('separatorGap', null);
if (!empty($gap)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($gap->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($gap->{$deviceKey})) {
         $style->addCss("margin-top", $gap->{$deviceKey}->value . 'px', $device);
         $style->addCss("margin-bottom", $gap->{$deviceKey}->value . 'px', $device);
      }
   }
}


$alignment = $element->params->get('separatorAlignment', null);
if (!empty($alignment)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($alignment->{$deviceKey})) {
         switch ($alignment->{$deviceKey}) {
            case "left":
               $style->addCss('margin-left', '0', $device);
               $style->addCss('margin-right', 'auto', $device);
               break;
            case "right":
               $style->addCss('margin-left', 'auto', $device);
               $style->addCss('margin-right', '0', $device);
               break;
            case "center":
               $style->addCss('margin-left', 'auto', $device);
               $style->addCss('margin-right', 'auto', $device);
               break;
         }
      }
   }
}

$borderRadius = $element->params->get('separatorRadius', null);
if (!empty($borderRadius)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($borderRadius->{$deviceKey}) && !empty($borderRadius->{$deviceKey})) {

         $css = \JDPageBuilder\Helper::spacingValue($borderRadius->{$deviceKey}, "radius");
         if (!empty($css)) {
            $style->addStyle($css, $device);
         }
      }
   }
}
?>