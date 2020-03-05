<?php
/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

$items = $element->params->get('items', []);
if (empty($items)) {
   return;
}

$element->addClass('jdb-tabs');
$tabType = $element->params->get('tabType', 'horizontal');
$element->addClass('jdb-tabs-' . $tabType);
if ($tabType == 'horizontal') {
   $element->addClass('jdb-tabs-align-left jdb-tabs-top');
} else {
   $element->addClass('jdb-tabs-left');
}
?>
<ul jdb-tab>
   <?php foreach ($items as $item) { ?>
      <li>
         <a class="jdb-tab-title jdb-icon-<?php echo $element->params->get('tabsIconPosition', 'left'); ?>" href="#">
            <?php
            if (!empty(@$item->icon)) {
               \JDPageBuilder\Builder::loadFontLibraryByIcon(@$item->icon);
               ?>
               <i class="jdb-tab-icon <?php echo $item->icon; ?>"></i>
            <?php } ?>
            <span><?php echo $item->title; ?></span>
         </a>
      </li>
   <?php } ?>
</ul>

<ul class="jdb-tab-contents">
   <?php foreach ($items as $item) { ?>
      <li class="jdb-tab-content">
         <?php echo $item->content; ?>
      </li>
   <?php } ?>
</ul>

<?php
$tabsStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-tab');
$tabsBorderStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-tab:after');
$tabStyleLi = new JDPageBuilder\Element\ElementStyle('> .jdb-tab > li');
$tabStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-tab > li a');
$tabStyleHover = new JDPageBuilder\Element\ElementStyle('> .jdb-tab > li:hover a');
$tabStyleActive = new JDPageBuilder\Element\ElementStyle('> .jdb-tab > li.jdb-active a');
$contentStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-tab-contents > li.jdb-tab-content');
$iconStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-tab > li > a > .jdb-tab-icon');

$element->addChildrenStyle([$tabsStyle, $tabsBorderStyle, $tabStyleLi, $tabStyle, $tabStyleHover, $tabStyleActive, $contentStyle, $iconStyle]);

// tabs styling

if ($tabType == 'vertical') {
   $tabsVerticalWidth = $element->params->get('tabsVerticalWidth', null);

   if (!empty($tabsVerticalWidth)) {
      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($tabsVerticalWidth->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($tabsVerticalWidth->{$deviceKey})) {
            $tabsStyle->addCss("min-width", $tabsVerticalWidth->{$deviceKey}->value . $tabsVerticalWidth->{$deviceKey}->unit, $device);
            $tabsStyle->addCss("max-width", $tabsVerticalWidth->{$deviceKey}->value . $tabsVerticalWidth->{$deviceKey}->unit, $device);
            $tabsStyle->addCss("width", $tabsVerticalWidth->{$deviceKey}->value . $tabsVerticalWidth->{$deviceKey}->unit, $device);
         }
      }
   }
}

$tabStyle->addCss("color", $element->params->get('tabsColor', ''));
$tabStyleHover->addCss("color", $element->params->get('tabsColorHover', ''));
$tabStyleActive->addCss("color", $element->params->get('tabsColorActive', ''));

$typography = $element->params->get('tabsTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $tabStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}

$padding = $element->params->get('tabsPadding', null);
if (!empty($padding)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
         $tabStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
      }
   }
}

// content

$contentStyle->addCss("color", $element->params->get('contentColor', ''));
$contentStyle->addCss("background-color", $element->params->get('contentBackground', ''));

$typography = $element->params->get('tabContentTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $contentStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}

$padding = $element->params->get('tabContentPadding', null);
if (!empty($padding)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
         $contentStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
      }
   }
}

$contentBorderStyle = $element->params->get('contentBorderStyle', 'none');
$contentStyle->addCss("border-style", $contentBorderStyle);
if ($contentBorderStyle != 'none') {
   $borderWidth = $element->params->get('contentBorderWidth', null);
   if (!empty($borderWidth)) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
            $contentStyle->addStyle(JDPageBuilder\Helper::spacingValue($borderWidth->{$deviceKey}, "border"), $device);
         }
      }
   }
   $contentStyle->addCss("border-color", $element->params->get('contentBorderColor', ''));
}

$tabsIconSize = $element->params->get('tabsIconSize', null);
if (!empty($tabsIconSize)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($tabsIconSize->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($tabsIconSize->{$deviceKey})) {
         $iconStyle->addCss("font-size", $tabsIconSize->{$deviceKey}->value . 'px', $device);
      }
   }
}
?>