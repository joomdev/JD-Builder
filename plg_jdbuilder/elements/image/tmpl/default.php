<?php
/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

// params
$attrs = [];

// image source
$image = $element->params->get('image', '');
if (empty($image)) {
   return;
}

// image title and alt
$imageTitle = $element->params->get('title', '');
if (!empty($imageTitle)) {
   // $attrs[] = 'title="' . $imageTitle . '"';
   $attrs[] = 'alt="' . $imageTitle . '"';
}

// image caption
$caption = $element->params->get('caption', '');



// image link
$link = $element->params->get('link', '');


// attributes, classes and styles
$element->addClass('jdb-image');
if (!empty($caption)) {
   $element->addClass('has-caption');
}

$attrs = empty($attrs) ? '' : ' ' . implode(" ", $attrs);

if (!empty($link)) {
// link title
   $linktitle = "";
   if (!empty($imageTitle)) {
      $linktitle = $imageTitle;
   }
   $linkTitle = empty($linktitle) ? '' : ' title="' . $linktitle . '"';

   // link target
   $linkTargetBlank = $element->params->get('linkTargetBlank', FALSE);
   $linkTarget = $linkTargetBlank ? ' target="_blank"' : "";

   // link follow
   $linkNoFollow = $element->params->get('linkNoFollow', FALSE);
   $linkRel = $linkNoFollow ? ' rel="nofollow"' : "";
}
?>
<figure class="jdb-image-wrapper">
   <?php if (!empty($link)) { ?>
      <a class="jdb-image-link" href="<?php echo $link; ?>"<?php echo $linkTitle; ?><?php echo $linkTarget; ?><?php echo $linkRel; ?>>
      <?php } ?>
      <img src="<?php echo \JDPageBuilder\Helper::mediaValue($image); ?>"<?php echo $attrs; ?> />
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
?>
