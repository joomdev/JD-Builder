<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);
$content = $element->params->get('content', '', 'RAW');
$dropcap = $element->params->get('dropcap', false);
if (empty($content)) {
   return;
}

$dropcapHTML = '';

if ($dropcap) {
   $firstWord = JDPageBuilder\Helper::firstWord($content);
   $firstLetter = JDPageBuilder\Helper::firstLetter($firstWord);

   $content = preg_replace('/' . $firstWord . '/', substr($firstWord, 1), $content, 1);

   $dropcapHTML = '<span class="jdb-firstletter">' . $firstLetter . '</span>';

   $dropcapStyle = new JDPageBuilder\Element\ElementStyle('.jdb-firstletter');
   $element->addChildStyle($dropcapStyle);

   $typography = $element->params->get('dropcapTypography', null);
   if (!empty($typography)) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
            $dropcapStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
         }
      }
   }

   $dropcapStyle->addCss('color', $element->params->get("dropcapColor", ''));
   $dropcapStyle->addCss('background-color', $element->params->get("dropcapBackground", ''));
}

$element->addClass('jdb-element-content');
echo '<div class="jdb-content">' . $dropcapHTML . $content . '</div>';

$contentStyle = new JDPageBuilder\Element\ElementStyle('> .jdb-content');
$element->addChildStyle($contentStyle);

$typography = $element->params->get('contentTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $contentStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}
