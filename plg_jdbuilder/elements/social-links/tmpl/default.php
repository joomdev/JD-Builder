<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

$profiles = $element->params->get('socialLinks', []);
if (empty($profiles)) {
   return;
}
$element->addClass('jdb-social-links');
$display = $element->params->get('slDisplay', 'icon-only');
$element->addClass('jdb-social-links-' . $display);
if ($display == "icon-title") {
   $element->addClass('jdb-social-links-icon-' . $element->params->get('iconPosition', 'left'));
}

$animation = $element->params->get('slHoverAnimation', '');
if (!empty($animation)) {
   $animation = 'jdb-hover-' . $animation;
}

$colorStyle = $element->params->get('iconStyle', 'brand');
if ($colorStyle == "brand") {
   $element->addClass('jdb-brands-icons');
}
$invertedColors = $element->params->get('brandColorInverted', false);
?>
<ul>
   <?php
   $index = 0;
   foreach ($profiles as $profile) {
      if (empty($profile)) {
         continue;
      }
      \JDPageBuilder\Builder::loadFontLibraryByIcon($profile->icon);
      $index++;

      $linkTargetBlank = (isset($profile->linkTargetBlank) && $profile->linkTargetBlank) ? ' target="_blank"' : '';
      $linkNoFollow = (isset($profile->linkNoFollow) && $profile->linkNoFollow) ? ' rel="nofollow"' : '';
      ?>
      <li class="jdb-social-link-<?php echo $index; ?> <?php echo $animation; ?>">
         <a data-brand="<?php echo str_replace(" ", "-", $profile->icon); ?>" title="<?php echo $profile->title; ?>" class="brand-<?php echo $invertedColors ? 'inverted' : 'static'; ?>" href="<?php echo $profile->link; ?>" <?php echo $linkTargetBlank; ?><?php echo $linkNoFollow; ?>>
            <span class="jdb-sl-icon">
               <span class="<?php echo $profile->icon; ?>"></span>
            </span>
            <span class="jdb-sl-title"><?php echo $profile->title; ?></span>
         </a>
      </li>
   <?php } ?>
</ul>

<?php
// Styling


$slAlignment = $element->params->get('slAlignment', null);
if (!empty($slAlignment)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($slAlignment->{$deviceKey}) && !empty($slAlignment->{$deviceKey})) {
         $element->addCss("text-align", $slAlignment->{$deviceKey}, $device);
      }
   }
}

$linkStyle = new JDPageBuilder\Element\ElementStyle("> ul li a");
$linkHoverStyle = new JDPageBuilder\Element\ElementStyle("> ul li:hover a");

$element->addChildrenStyle([$linkStyle, $linkHoverStyle]);


$borderRadius = $element->params->get('slBorderRadius', null);
if (!empty($borderRadius)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($borderRadius->{$deviceKey}) && !empty($borderRadius->{$deviceKey})) {
         $linkStyle->addStyle(JDPageBuilder\Helper::spacingValue($borderRadius->{$deviceKey}, "radius"), $device);
      }
   }
}

$padding = $element->params->get('innerPadding', null);
if (!empty($padding)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($padding->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($padding->{$deviceKey})) {
         $linkStyle->addCss("padding", $padding->{$deviceKey}->value . 'px', $device);
      }
   }
}

$spaceBetween = $element->params->get('slSpaceBetween', null);
if (!empty($spaceBetween)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($spaceBetween->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($spaceBetween->{$deviceKey})) {
         $linkStyle->addCss("margin-right", 'calc(' . $spaceBetween->{$deviceKey}->value . 'px / 2)', $device);
         $linkStyle->addCss("margin-left", 'calc(' . $spaceBetween->{$deviceKey}->value . 'px / 2)', $device);
      }
   }
}

$borderStyle = $element->params->get('slBorderStyle', 'none');
$linkStyle->addCss("border-style", $borderStyle);
if ($borderStyle != "none") {
   $borderWidth = $element->params->get('slBorderWidth', null);
   if (!empty($borderWidth)) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
            $linkStyle->addStyle(JDPageBuilder\Helper::spacingValue($borderWidth->{$deviceKey}, "border"), $device);
         }
      }
   }
}

$linkStyle->addCss("box-shadow", $element->params->get('slBoxShadow', ''));
if ($colorStyle != "brand") {
   $linkStyle->addCss("color", $element->params->get('slColor', ''));
}
if (!($colorStyle == "brand" && $invertedColors)) {
   $linkHoverStyle->addCss("color", $element->params->get('slHoverColor', ''));
}
if ($colorStyle != "brand") {
   $linkStyle->addCss("background-color", $element->params->get('slBackgroundColor', ''));
}
if (!($colorStyle == "brand" && $invertedColors)) {
   $linkHoverStyle->addCss("background-color", $element->params->get('slHoverBackgroundColor', ''));
}
if ($colorStyle != "brand") {
   $linkStyle->addCss("border-color", $element->params->get('slBorderColor', ''));
}
if (!($colorStyle == "brand" && $invertedColors)) {
   $linkHoverStyle->addCss("border-color", $element->params->get('slBorderHoverColor', ''));
}
if ($display != 'title-only') {
   $iconStyle = new JDPageBuilder\Element\ElementStyle("> ul li a .jdb-sl-icon");
   $element->addChildStyle($iconStyle);

   $iconSize = $element->params->get('iconSize', null);
   if (!empty($iconSize)) {
      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($iconSize->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($iconSize->{$deviceKey})) {
            $iconStyle->addCss("font-size", $iconSize->{$deviceKey}->value . 'px', $device);
            $iconStyle->addCss("width", $iconSize->{$deviceKey}->value . 'px', $device);
            $iconStyle->addCss("height", $iconSize->{$deviceKey}->value . 'px', $device);
         }
      }
   }
}

if ($display != 'icon-only') {
   $textStyle = new JDPageBuilder\Element\ElementStyle("> ul li a .jdb-sl-title");
   $element->addChildStyle($textStyle);

   $textSize = $element->params->get('textSize', null);
   if (!empty($textSize)) {
      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($textSize->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($textSize->{$deviceKey})) {
            $textStyle->addCss("font-size", $textSize->{$deviceKey}->value . 'px', $device);
         }
      }
   }
}
?>