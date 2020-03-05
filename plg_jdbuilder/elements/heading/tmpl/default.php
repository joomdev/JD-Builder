<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

$element->addClass('jdb-heading');

// Params
$title = $element->params->get("title", "");
$subtitle = $element->params->get("subtitle", "");


// link
$link = $element->params->get("link", "");

$linkTargetBlank = $element->params->get('linkTargetBlank', false);
$linkTarget = $linkTargetBlank ? ' target="_blank"' : "";

$linkNoFollow = $element->params->get('linkNoFollow', false);
$linkRel = $linkNoFollow ? ' rel="nofollow"' : "";

$alignment = $element->params->get('headingAlignment', null);
if (!empty($alignment)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($alignment->{$deviceKey}) && !empty($alignment->{$deviceKey})) {
         $element->addCss('text-align', $alignment->{$deviceKey}, $device);
      }
   }
}

$subtitlePosition = $element->params->get('subtitlePosition', 'below');

$headingHtmlTag = $element->params->get("headingHtmlTag", "h3");
$subheadingHtmlTag = $element->params->get("subheadingHtmlTag", "span");
$headingHTML = '<' . $headingHtmlTag . ' class="jdb-heading-heading">' . $title . '</' . $headingHtmlTag . '/>';
$subHeadingHTML = !empty($subtitle) ? '<' . $subheadingHtmlTag . ' class="jdb-heading-subheading">' . $subtitle . '</' . $subheadingHtmlTag . '>' : '';


if ($subtitlePosition == "above") {
   echo $subHeadingHTML;
}
?>
<<?php echo $headingHtmlTag; ?> class="jdb-heading-heading">

   <?php
   if (!empty($link)) {
   ?>
      <a title="<?php echo $title; ?>" href="<?php echo $link; ?>" <?php echo $linkTarget; ?><?php echo $linkRel; ?>>
      <?php
   }

   echo $title;
   if (!empty($link)) {
      ?>
      </a>
   <?php
   }
   ?>
</<?php echo $headingHtmlTag; ?>>

<?php
if ($subtitlePosition == "below") {
   echo $subHeadingHTML;
}

foreach (['heading', 'subheading'] as $heading) {

   $style = new JDPageBuilder\Element\ElementStyle("> .jdb-heading-" . $heading);
   $element->addChildStyle($style);

   if ($heading == 'heading') {
      $headingSpacing = $element->params->get('headingSpacing', null);
      if ($headingSpacing != null) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($headingSpacing->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($headingSpacing->{$deviceKey})) {
               $mPos = $element->params->get('subtitlePosition', 'below') == 'below' ? 'margin-bottom' : 'margin-top';
               $style->addCss($mPos, $headingSpacing->{$deviceKey}->value . "px", $device);
            }
         }
      }
   }

   // color
   $style->addCss("color", $element->params->get($heading . "FontColor", ""));
   $style->addCss("text-shadow", $element->params->get($heading . "TextShadow", ""));

   // typography
   $typography = $element->params->get($heading . "Typography", NULL);
   if (!empty($typography)) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
            $style->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
         }
      }
   }
}
?>