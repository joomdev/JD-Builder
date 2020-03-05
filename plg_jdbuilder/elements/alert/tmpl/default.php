<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

// Params

$defaultContent = $element->params->exists('alertContent') ? '' : 'Alert Text';
$content = $element->params->get("alertContent", $defaultContent);
$heading = $element->params->get("alertHeading", "");

if (empty($content) && empty($heading)) {
   return;
}
$class = [];
$alertHTML = [];

// Alert Icon
$icon = $element->params->get("alertIcon", "");
$iconHTML = "";
if (!empty($icon)) {
   \JDPageBuilder\Builder::loadFontLibraryByIcon($icon);
   $iconHTML = '<span class="jdb-alert-icon ' . $icon . '"></span>';
}

// Alert Dismiss
$dismissButton = $element->params->get("dismissButton", false);
$dismissHTML = "";
if ($dismissButton) {
   $class[] = 'jdb-alert-dismissible';
   $dismissHTML = '<a href="#" class="jdb-close jdb-alert-close"><span aria-hidden="true">&times;</span></a>';
}

// Alert Heading
if (!empty($heading)) {
   $alertHTML[] = '<h4 class="jdb-alert-heading">';
   $alertHTML[] = $iconHTML;
   $alertHTML[] = $heading;
   $alertHTML[] = '</h4>';
}

// Alert Content
if (!empty($content)) {
   $alertHTML[] = '<div class="jdb-alert-content">';
   if (empty($heading)) {
      $alertHTML[] = $iconHTML;
   }
   $alertHTML[] = $content;
   $alertHTML[] = '</div>';
}

// Alert Type
$type = $element->params->get("alertType", "success");
if (!empty($type)) {
   $class[] = 'jdb-alert-' . $type;
}

$alertHTML[] = $dismissHTML;

// Display Alert
echo '<div jdb-alert class="jdb-alert' . (!empty($class) ? ' ' . implode(' ', $class) : '') . '">' . implode("", $alertHTML) . '</div>';
?>
<?php

$contentStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-alert > .jdb-alert-content');
$headingStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-alert > .jdb-alert-heading');
$element->addChildrenStyle([$contentStyle, $headingStyle]);

$typography = $element->params->get('alertContentTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $contentStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}

$typography = $element->params->get('alertHeadingTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $headingStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}
?>