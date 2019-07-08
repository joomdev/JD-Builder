<?php
defined('_JEXEC') or die;
extract($displayData);

$items = $element->params->get('items', []);
if (empty($items)) {
   return;
}

$element->addClass('jdb-accordion-element');

$collapsible = $element->params->get('accordionCollapsible', true);
$multiple = $element->params->get('accordionMultiple', false);
if ($collapsible) {
   $firstactive = $element->params->get('accordionFirstActive', true);
} else {
   $firstactive = false;
}
if ($multiple) {
   $opanAll = $element->params->get('accordionOpenAll', false);
} else {
   $opanAll = false;
}

$titleTag = $element->params->get('titleTag', '');
$titleTag = empty($titleTag) ? 'span' : $titleTag;
?>

<ul jdb-accordion="collapsible:<?php echo $collapsible ? 'true' : 'false'; ?>;active:<?php echo $firstactive ? 0 : 'false'; ?>;multiple:<?php echo $multiple ? 'true' : 'false'; ?>">
   <?php foreach ($items as $item) { ?>
      <li<?php echo $opanAll ? ' class="jdb-active"' : ''; ?>>
         <a class="jdb-accordion-title jdb-caret-<?php echo $element->params->get('accordionIconAlignment', 'right'); ?>" href="#">
            <<?php echo $titleTag; ?> class="jdb-accordion-text">
            <?php
            if (!empty(@$item->icon)) {
               \JDPageBuilder\Builder::loadFontLibraryByIcon(@$item->icon);
               ?>
               <i class="jdb-accordion-icon <?php echo $item->icon; ?>"></i>
            <?php } ?>
            <?php echo $item->title; ?>
            </<?php echo $titleTag; ?>>
            <?php
            echo JDPageBuilder\Helper::getCaretValue($element->params->get('accordionIcon', 'plus'));
            ?>
         </a>
         <div class="jdb-accordion-content">
            <div>
               <?php echo JDPageBuilder\Helper::renderHTML($item->content); ?>
            </div>
         </div>
      </li>
   <?php } ?>
</ul>

<?php
$itemStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li');
$itemHoverStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li:not(.jdb-active):hover');
$itemActiveStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li.jdb-active');

$titleStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li > .jdb-accordion-title');
$titleTextStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li > .jdb-accordion-title .jdb-accordion-text');
$titleHoverStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li:not(.jdb-active):hover > .jdb-accordion-title');
$titleActiveStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li.jdb-active > .jdb-accordion-title');
$titleActiveTextStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li.jdb-active > .jdb-accordion-title .jdb-accordion-text');


$contentStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li > div > div');

$caretStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li > a > .jdb-caret');
$caretActiveStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-accordion > li.jdb-active > a > .jdb-caret');


$element->addChildrenStyle([$itemStyle, $itemHoverStyle, $itemActiveStyle, $titleStyle, $titleTextStyle, $titleHoverStyle, $titleActiveStyle, $contentStyle, $caretStyle, $caretActiveStyle, $titleActiveTextStyle]);


// Item Styling
$spaceBetween = $element->params->get('itemSpaceBetween', null);
if (!empty($spaceBetween)) {
   foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($spaceBetween->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($spaceBetween->{$deviceKey})) {
         $itemStyle->addCss("margin-bottom", $spaceBetween->{$deviceKey}->value . 'px', $device);
      }
   }
}

$itemBorderStyle = $element->params->get('itemBorderStyle', 'none');
$itemStyle->addCss("border-style", $itemBorderStyle);
if ($itemBorderStyle != 'none') {
   $borderWidth = $element->params->get('itemBorderWidth', null);
   if (!empty($borderWidth)) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
            $css = JDPageBuilder\Helper::spacingValue($borderWidth->{$deviceKey}, "border");
            $itemStyle->addStyle($css, $device);
         }
      }
   } else {
      $itemStyle->addCss("border-width", "1px");
   }

   $itemStyle->addCss("border-color", $element->params->get('itemBorderColor', ''));
   $itemHoverStyle->addCss("border-color", $element->params->get('itemBorderColorHover', ''));
   $itemActiveStyle->addCss("border-color", $element->params->get('itemBorderColorActive', ''));
}

$borderRadius = $element->params->get('itemBorderRadius', null);
if (!empty($borderRadius)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($borderRadius->{$deviceKey}) && !empty($borderRadius->{$deviceKey})) {
         $css = JDPageBuilder\Helper::spacingValue($borderRadius->{$deviceKey}, "radius");
         $itemStyle->addStyle($css, $device);
         $titleStyle->addStyle($css, $device);
         $contentStyle->addStyle($css, $device);

         $titleStyle->addCss("border-bottom-right-radius", 0, $device);
         $titleStyle->addCss("border-bottom-left-radius", 0, $device);
         $contentStyle->addCss("border-top-right-radius", 0, $device);
         $contentStyle->addCss("border-top-left-radius", 0, $device);
      }
   }
}

$boxShadow = $element->params->get('itemBoxShadow', null);
if (!empty($boxShadow)) {
   $itemStyle->addCss('box-shadow', $boxShadow);
}

// Title Styling

$titleTextStyle->addCss("color", $element->params->get('titleColor', ''));

$typography = $element->params->get('accordionTitleTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $titleTextStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
         if (isset($typography->{$deviceKey}->size) && is_numeric($typography->{$deviceKey}->size)) {
            $caretStyle->addCss("min-width", "calc(" . $typography->{$deviceKey}->size . $typography->{$deviceKey}->sizeUnit . " + 10px)", $device);
            $caretStyle->addCss("max-width", "calc(" . $typography->{$deviceKey}->size . $typography->{$deviceKey}->sizeUnit . " + 10px)", $device);
            $caretStyle->addCss("min-height", "calc(" . $typography->{$deviceKey}->size . $typography->{$deviceKey}->sizeUnit . " + 10px)", $device);
            $caretStyle->addCss("max-width", "calc(" . $typography->{$deviceKey}->size . $typography->{$deviceKey}->sizeUnit . " + 10px)", $device);
            $caretStyle->addCss("line-height", "calc(" . $typography->{$deviceKey}->size . $typography->{$deviceKey}->sizeUnit . " + 10px)", $device);
            $caretStyle->addCss("font-size", $typography->{$deviceKey}->size . $typography->{$deviceKey}->sizeUnit, $device);
         }
      }
   }
}

$padding = $element->params->get('accordionTitlePadding', null);
if (!empty($padding)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
         $titleStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
      }
   }
} else {
   $titleStyle->addCss("padding", "10px");
}

$titleBorderStyle = $element->params->get('titleBorderStyle', 'solid');
$titleStyle->addCss("border-style", $titleBorderStyle);
if ($titleBorderStyle != 'none') {
   $borderWidth = $element->params->get('titleBorderWidth', null);
   if ($borderWidth != null) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
            $titleStyle->addStyle(JDPageBuilder\Helper::spacingValue($borderWidth->{$deviceKey}, "border"), $device);
         }
      }
   }

   $titleStyle->addCss("border-color", $element->params->get('titleBorderColor', ''));
   $titleHoverStyle->addCss("border-color", $element->params->get('titleBorderColorHover', ''));
   $titleActiveStyle->addCss("border-color", $element->params->get('titleBorderColorActive', ''));
}

// hover

$titleStyle->addCss("background-color", $element->params->get('titleBackground', ''));
$titleActiveTextStyle->addCss("color", $element->params->get('titleColorActive', ''));
$titleActiveStyle->addCss("background-color", $element->params->get('titleBackgroundActive', ''));

$contentStyle->addCss("color", $element->params->get('contentColor', ''));
$contentStyle->addCss("background-color", $element->params->get('contentBackground', ''));
$contentStyle->addCss("background-color", $element->params->get('contentBackground', ''));

// Content Styling

$typography = $element->params->get('accordionContentTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $contentStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}

$padding = $element->params->get('accordionContentPadding', null);
if (!empty($padding)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
         $contentStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
      }
   }
} else {
   $contentStyle->addCss("padding", "10px");
}


$contentBorderStyle = $element->params->get('contentBorderStyle', 'solid');
$contentStyle->addCss("border-style", $contentBorderStyle);

if ($contentBorderStyle != 'none') {
   $borderWidth = $element->params->get('contentBorderWidth', null);
   if ($borderWidth != null) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
            $css = JDPageBuilder\Helper::spacingValue($borderWidth->{$deviceKey}, "border");
            $contentStyle->addStyle($css, $device);
         }
      }
   }

   $contentStyle->addCss("border-color", $element->params->get('contentBorderColor', ''));
}


// caret

$caretStyle->addCss("color", $element->params->get('caretColor', ''));
$caretStyle->addCss("background-color", $element->params->get('caretBackgroundColor', ''));
$caretActiveStyle->addCss("color", $element->params->get('caretColorActive', ''));
$caretActiveStyle->addCss("background-color", $element->params->get('caretBackgroundColorActive', ''));
?>