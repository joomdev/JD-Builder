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
$attrs = [];

$document = \JDPageBuilder\Builder::getDocument();
// image source
$image = $element->params->get('image', '');
if (empty($image)) {
   return;
}

// image title and alt
$imageTitle = $element->params->get('title', '');
if (!empty($imageTitle)) {
   $attrs[] = 'title="' . $imageTitle . '"';
}

$imageAlt = $element->params->get('alt', '');
if (!empty($imageAlt)) {
   $attrs[] = 'alt="' . $imageAlt . '"';
}

// image caption
$caption = $element->params->get('caption', '');
$settings = \JDPageBuilder\Builder::getSettings();
$lightbox = $element->params->get('lightbox', '');
$lightbox = $lightbox == '' ? filter_var($settings->get('lightbox', true), FILTER_VALIDATE_BOOLEAN) : ($lightbox == '1' ? true : false);

// image link
$linkType = $element->params->get('linkType', '');
if ($linkType == 'none') {
   $link = "";
} else if ($linkType == "media") {
   $link = \JDPageBuilder\Helper::mediaValue($image);
   $linkTarget = ' target="_blank"';
   $linkRel = "";
   $linkLightbox = "";

   if ($lightbox) {
      $linkLightbox = ' data-jdb-lightbox="lightbox-' . $element->id . '" data-jdb-lightbox-caption="' . \JDPageBuilder\Helper::getLightboxContent('description', $imageTitle, $caption, $imageAlt) . '" data-jdb-lightbox-title="' . \JDPageBuilder\Helper::getLightboxContent('title', $imageTitle, $caption, $imageAlt) . '"';
      JDPageBuilder\Builder::loadLightBox();
   }
} else {
   $link = $element->params->get('link', '');
   // link target
   $linkTargetBlank = $element->params->get('linkTargetBlank', FALSE);
   $linkTarget = $linkTargetBlank ? ' target="_blank"' : "";

   // link follow
   $linkNoFollow = $element->params->get('linkNoFollow', FALSE);
   $linkRel = $linkNoFollow ? ' rel="nofollow"' : "";
   $linkLightbox = "";
}

// attributes, classes and styles
$element->addClass('jdb-image');
if (!empty($caption)) {
   $element->addClass('has-caption');
}

$attrs = empty($attrs) ? '' : ' ' . implode(" ", $attrs);
?>
<figure class="jdb-image-wrapper">
   <?php if (!empty($link)) { ?>
      <a class="jdb-image-link" href="<?php echo $link; ?>" <?php echo $linkTarget . $linkRel . $linkLightbox; ?>>
      <?php } ?>
      <img src="<?php echo \JDPageBuilder\Helper::mediaValue($image); ?>" <?php echo $attrs; ?> />
      <?php if (!empty($caption)) { ?>
         <figcaption class="jdb-image-caption"><?php echo $caption; ?></figcaption>
      <?php } ?>
      <?php if (!empty($link)) { ?>
      </a>
   <?php } ?>
</figure>
<?php
// image alignment

foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
   $alignment = $element->params->get('alignment', null);
   if (!empty($alignment) && isset($alignment->{$deviceKey}) && !empty($alignment->{$deviceKey})) {
      $element->addCss("text-align", $alignment->{$deviceKey}, $device);
   }
}

// image size
$imageStyle = new JDPageBuilder\Element\ElementStyle("img");
$element->addChildStyle($imageStyle);

if ($element->params->get('imageSize', 'original') == "custom") {
   $width = $element->params->get('width', null);
   if (!empty($width)) {
      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($width->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
            $imageStyle->addCss("width", $width->{$deviceKey}->value . $width->{$deviceKey}->unit, $device);
         }
      }
   }

   $width = $element->params->get('maxWidth', null);
   if (!empty($width)) {
      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($width->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
            $imageStyle->addCss("max-width", $width->{$deviceKey}->value . $width->{$deviceKey}->unit, $device);
         }
      }
   }
}

JDPageBuilder\Helper::applyBorderValue($imageStyle, $element->params, "imageBorder");
if ($document->lightBox) {
?>
   <script>
      refreshJDLightbox();
   </script>
<?php } ?>