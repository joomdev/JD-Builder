<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

// params
$defaultIcon = $element->params->exists('icon') ? '' : 'fab fa-joomla';
$icon = $element->params->get('icon', $defaultIcon);
if (empty($icon)) {
   return;
}
\JDPageBuilder\Builder::loadFontLibraryByIcon($icon);
$element->addClass('jdb-icon');

// button link
$link = $element->params->get('link', '');

// link target
$linkTargetBlank = $element->params->get('linkTargetBlank', FALSE);
$linkTarget = $linkTargetBlank ? ' target="_blank"' : "";

// link follow
$linkNoFollow = $element->params->get('linkNoFollow', FALSE);
$linkRel = $linkNoFollow ? ' rel="nofollow"' : "";
$animation = $element->params->get('iconHoverAnimation', '');
$animation = empty($animation) ? '' : ' jdb-hover-' . $animation;
?>

<?php if (!empty($link)) { ?>
   <a class="jdb-icon-wrapper<?php echo $animation; ?>" href="<?php echo $link; ?>" <?php echo $linkTarget; ?><?php echo $linkRel; ?>>
   <?php } else { ?>
      <div class="jdb-icon-wrapper<?php echo $animation; ?>">
      <?php } ?>
      <span class="<?php echo $icon; ?>"></span>
      <?php if (!empty($link)) { ?>
   </a>
<?php } else { ?>
   </div>
<?php } ?>

<?php
$alignment = $element->params->get('iconAlignment', null);
if (!empty($alignment)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($alignment->{$deviceKey}) && !empty($alignment->{$deviceKey})) {
         $element->addCss('text-align', $alignment->{$deviceKey}, $device);
      }
   }
}

$iconStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-icon-wrapper');
$iconInnerStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-icon-wrapper > span');
$iconHoverStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-icon-wrapper:hover');

$element->addChildrenStyle([$iconStyle, $iconHoverStyle, $iconInnerStyle]);

switch ($element->params->get('iconShape', 'circle')) {
   case 'rounded':
      $borderRadius = $element->params->get('iconBorderRadius', null);
      if (!empty($borderRadius)) {
         foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($borderRadius->{$deviceKey}) && !empty($borderRadius->{$deviceKey})) {
               $css = JDPageBuilder\Helper::spacingValue($borderRadius->{$deviceKey}, "radius");
               $iconStyle->addStyle($css, $device);
            }
         }
      }
      break;
   case 'circle':
      $iconStyle->addCss("border-radius", "50%");
      break;
   case 'square':
      $iconStyle->addCss("border-radius", "0");
      break;
}

// Icon Size
$iconSize = $element->params->get('iconSize', null);
if (!empty($iconSize)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($iconSize->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($iconSize->{$deviceKey})) {
         $iconStyle->addCss("font-size", ($iconSize->{$deviceKey}->value * 0.70) . 'px', $device);
         $iconStyle->addCss("width", $iconSize->{$deviceKey}->value . 'px', $device);
         $iconStyle->addCss("height", $iconSize->{$deviceKey}->value . 'px', $device);
         $iconStyle->addCss("line-height", $iconSize->{$deviceKey}->value . 'px', $device);
      }
   }
}

// Icon Rotate
$iconRotate = $element->params->get('iconRotate', null);
if (!empty($iconRotate)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($iconRotate->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($iconRotate->{$deviceKey}) && !empty($iconRotate->{$deviceKey}->value)) {
         $iconInnerStyle->addCss("transform", 'rotate(' . $iconRotate->{$deviceKey}->value . 'deg)', $device);
      }
   }
}

// Padding
$padding = $element->params->get('iconPadding', null);
if (!empty($padding)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
         $iconStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
      }
   }
}

$iconStyle->addCss("color", $element->params->get('iconTextColor', ''));
$iconStyle->addCss("background-color", $element->params->get('iconBackgroundColor', ''));
$iconStyle->addCss("border-color", $element->params->get('iconBorderColor', ''));
$iconStyle->addCss("background-image", $element->params->get('iconGradient', ''));
$iconStyle->addCss("box-shadow", $element->params->get('iconBoxShadow', ''));


$iconHoverStyle->addCss("color", $element->params->get('iconTextColorHover', ''));
$iconHoverStyle->addCss("background-color", $element->params->get('iconBackgroundColorHover', ''));
$iconHoverStyle->addCss("border-color", $element->params->get('iconBorderColorHover', ''));
$iconHoverStyle->addCss("background-image", $element->params->get('iconGradientHover', ''));

// border
$iconBorderStyle = $element->params->get('iconBorderStyle', 'none');
$iconStyle->addCss("border-style", $iconBorderStyle);

if ($iconBorderStyle != 'none') {
   $borderWidth = $element->params->get('iconBorderWidth', null);
   if ($borderWidth != null) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
            $iconStyle->addStyle(JDPageBuilder\Helper::spacingValue($borderWidth->{$deviceKey}, "border"), $device);
         }
      }
   }
}
?>