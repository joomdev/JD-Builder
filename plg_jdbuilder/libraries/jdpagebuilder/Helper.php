<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

use JDPageBuilder\Element\ElementStyle;
use JDPageBuilder\Element\Layout;
use Leafo\ScssPhp\Compiler;
use Mustache_Autoloader;
use Mustache_Engine;
use Symfony\Component\HttpClient\CurlHttpClient;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\OutputFormat;

Mustache_Autoloader::register();
\JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
\JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

class Helper
{

   public static $devices = ['md' => 'desktop', 'sm' => 'tablet', 'xs' => 'mobile'];
   public static $css_cache = false;
   private static $_router = array();

   public static function classify($word)
   {
      return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
   }

   public static function linkify($text)
   {
      // The Regular Expression filter
      $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
      // Check if there is a url in the text
      if (preg_match($reg_exUrl, $text, $url)) {

         // make the urls hyper links
         return preg_replace($reg_exUrl, "<a target='_blank' href='{$url[0]}'>{$url[0]}</a> ", $text);
      } else {
         // if no urls in the text just return the text
         return $text;
      }
   }

   public static function log($title = '', $txt = '')
   {
      $dir = JPATH_SITE . '/tmp/jdblogs';
      if (!file_exists($dir)) {
         mkdir($dir, 0777);
      }
      file_put_contents($dir . '/' . $title . '-' . date('d-M-Y-H-i-s') . '.txt', $txt);
   }

   public static function isBuilderDemo()
   {
      $config = \JFactory::getConfig();
      return $config->get('jdbuilder_demo', 0);
   }

   public static function titlecase($word)
   {
      return ucfirst(str_replace(['_', '-'], ' ', ucwords($word, '_-')));
   }

   public static function camelize($word)
   {
      return lcfirst(self::classify($word));
   }

   public static function tableize($word)
   {
      $tableized = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $word);
      return mb_strtolower($tableized);
   }

   public static function loadLanguage($name = "jdbuilder", $path = "")
   {
      if (empty($path)) {
         $path = JPATH_PLUGINS . '/system/jdbuilder';
      }
      $lang = \JFactory::getLanguage();
      $lang->load($name, $path);
      $lang->load('com_jdbuilder', JPATH_ADMINISTRATOR);
   }

   public static function jsonDecode($string, $assoc = false)
   {
      $array = \json_decode($string, $assoc);
      if (empty($array)) {
         return [];
      }
      return $array;
   }

   public static function getPluginParams()
   {
      $plugin = \JPluginHelper::getPlugin('system', 'jdbuilder');
      $params = new \JRegistry($plugin->params);
      return $params;
   }

   public static function getLayout($id)
   {
      $db = \JFactory::getDbo();
      $db->setQuery("SELECT * FROM `#__jdbuilder_layouts` WHERE `id`='{$id}'");
      return $db->loadObject();
   }

   public static function getPage($id)
   {
      $db = \JFactory::getDbo();
      $db->setQuery("SELECT * FROM `#__jdbuilder_pages` WHERE `id`='{$id}'");
      return $db->loadObject();
   }

   public static function getFieldsGroup($name, $type)
   {
      $files = Builder::getXMLFilesByElementType($type);
      $files = array_reverse($files);
      $xml = null;
      foreach ($files as $file) {
         $pathinfo = pathinfo($file);
         $xmlfile = $pathinfo['dirname'] . '/' . $name . '.xml';
         if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);
            break;
         }
      }
      if ($xml == null) {
         return [];
      }
      return $xml->field;
   }

   public static function getQueryString($url, $key)
   {
      $parts = parse_url($url);
      $return = null;
      if (isset($parts['query'])) {
         parse_str($parts['query'], $query);
         if (isset($query[$key])) {
            $return = $query[$key];
         }
      }
      return $return;
   }

   public static function getVideoContent($params)
   {
      $type = $params->get('videoType', 'youtube');
      if ($type != 'upload') {
         $link = $params->get('videoLink', '');
         if (empty($link)) {
            return;
         }
      } else {
         $videoMedia = $params->get('videoMedia', '');
         if (empty($videoMedia)) {
            return;
         }
         $link = Helper::mediaValue($videoMedia);
      }

      $autoplay = $params->get('videoAutoplay', FALSE);
      $mute = $params->get('videoMute', FALSE);
      $loop = $params->get('videoLoop', FALSE);
      $controls = $params->get('videoControls', TRUE);

      switch ($type) {
         case "youtube":
            return self::getYoutubeVideoByLink($params, $link, $autoplay, $mute, $loop, $controls);
            break;
         case "vimeo":
            return self::getVimeoVideoByLink($params, $link, $autoplay, $mute, $loop, $controls);
            break;
         case "dailymotion":
            return self::getDailyMotionVideoByLink($params, $link, $autoplay, $mute, $loop, $controls);
            break;
         case "upload":
            return self::getVideoByLink($params, $link, $autoplay, $mute, $loop, $controls);
            break;
      }
   }

   public static function getBGVideoContent($params)
   {
      $type = $params->get('backgroundVideoType', 'none');
      $type = "upload";
      if ($type == "none") {
         return "";
      }
      if ($type != 'upload') {
         $link = $params->get('backgroundVideoLink', '');
         if (empty($link)) {
            return "";
         }
      } else {
         $videoMedia = $params->get('backgroundVideoMedia', '');
         if (empty($videoMedia)) {
            return "";
         }
         $link = Helper::mediaValue($videoMedia);
      }

      switch ($type) {
         case "youtube":
            $videoId = self::getQueryString($link, 'v');
            if (empty($videoId)) {
               return "";
            }
            return '<iframe allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="jdb-embed-responsive-item" src="https://www.youtube-nocookie.com/embed/' . $videoId . '?controls=0&mute=1&showinfo=0&rel=0&autoplay=1&loop=1&playlist=' . $videoId . '"></iframe>';
            break;
         case "vimeo":
            $videoId = substr(parse_url($link, PHP_URL_PATH), 1);
            if (empty($videoId)) {
               return "";
            }
            return '<iframe class="jdb-embed-responsive-item" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen src="https://player.vimeo.com/video/' . $videoId . '?autoplay=1&muted=1&loop=1&controls=0"></iframe>';
            break;
         case "dailymotion":
            $videoId = substr(parse_url($link, PHP_URL_PATH), 1);
            if (empty($videoId)) {
               return "";
            }
            return '<iframe class="jdb-embed-responsive-item" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen src="//www.dailymotion.com/embed/' . $videoId . '?mute=1&autoplay=1&controls=0&loop=1"></iframe>';
            break;
         case "upload":
            return '<video autoplay muted loop playsinline><source src="' . $link . '" type="video/mp4"></video>';
            break;
      }
   }

   public static function getYoutubeVideoByLink($params, $link, $autoplay, $mute, $loop, $controls)
   {

      $modestBranding = $params->get('videoModestBranding', false);
      $privacyMode = $params->get('videoPrivacyMode', false);
      $suggestedVideos = $params->get('videoSuggestedVideos', false);
      $start = $params->get('videoStartTime', 0);
      $end = $params->get('videoEndTime', 0);

      $videoId = self::getQueryString($link, 'v');
      if (empty($videoId)) {
         return false;
      }
      $attrs = [];
      if ($autoplay) {
         $attrs[] = 'autoplay=1';
      }
      if ($mute) {
         $attrs[] = 'mute=1';
      }
      if ($loop) {
         $attrs[] = 'loop=1';
         $attrs[] = 'playlist=' . $videoId;
      }
      if (!$controls) {
         $attrs[] = 'controls=0';
      }
      if ($modestBranding) {
         $attrs[] = 'modestbranding=1';
      }
      if (!empty($start)) {
         $attrs[] = 'start=' . $start;
      }
      if (!empty($end)) {
         $attrs[] = 'end=' . $end;
      }

      $attrs = empty($attrs) ? '' : '?' . implode('&', $attrs);

      return '<iframe allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="jdb-embed-responsive-item" src="https://www.youtube' . ($privacyMode ? '-nocookie' : '') . '.com/embed/' . $videoId . $attrs . '"></iframe>';
   }

   public static function getVimeoVideoByLink($params, $link, $autoplay, $mute, $loop, $controls)
   {

      $start = $params->get('videoStartTime', 0);

      $videoId = substr(parse_url($link, PHP_URL_PATH), 1);
      if (empty($videoId)) {
         return false;
      }
      $attrs = [];
      if ($autoplay) {
         $attrs[] = 'autoplay=1';
      }
      if ($mute) {
         $attrs[] = 'muted=1';
      }
      if ($loop) {
         $attrs[] = 'loop=1';
      }
      if (!$controls) {
         $attrs[] = 'controls=0';
      }
      $attrs[] = 'transparent=0';
      if (!empty($start)) {
         $attrs[] = '#t=' . $start . 's';
      }
      $attrs = empty($attrs) ? '' : '?' . implode('&', $attrs);

      return '<iframe class="jdb-embed-responsive-item" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen src="https://player.vimeo.com/video/' . $videoId . $attrs . '"></iframe>';
   }

   public static function getDailyMotionVideoByLink($params, $link, $autoplay, $mute, $loop, $controls)
   {

      $start = $params->get('videoStartTime', 0);

      $videoId = substr(parse_url($link, PHP_URL_PATH), 1);
      if (empty($videoId)) {
         return false;
      }
      $attrs = [];
      if ($autoplay) {
         $attrs[] = 'autoplay=1';
      }
      if ($mute) {
         $attrs[] = 'mute=1';
      }
      if (!$controls) {
         $attrs[] = 'controls=0';
      }
      if (!empty($start)) {
         $attrs[] = 'start=' . $start;
      }

      $attrs = empty($attrs) ? '' : '?' . implode('&', $attrs);

      return '<iframe class="jdb-embed-responsive-item" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen src="//www.dailymotion.com/embed/' . $videoId . $attrs . '"' . ($autoplay ? ' allow="autoplay"' : '') . ($autoplay ? ' allowfullscreen' : '') . '></iframe>';
   }

   public static function getVideoByLink($params, $link, $autoplay, $mute, $loop, $controls)
   {
      if ($params != null) {
         $start = $params->get('videoStartTime', 0);
         $end = $params->get('videoEndTime', 0);
      } else {
         $start = 0;
         $end = 0;
      }
      return '<video' . ($controls ? ' controls' : '') . '' . ($autoplay ? ' autoplay' : '') . '' . ($mute ? ' muted' : '') . '' . ($loop ? ' loop' : '') . ' ><source src="' . $link . '" type="video/mp4">Your browser does not support the video tag.</video>';
   }

   public static function minifyJS($files = [])
   {
      $cache_key = [];
      foreach ($files as $file) {
         $cache_key[] = md5_file($file);
      }
      $cache_key = md5(implode("", $cache_key));

      $cacheFolder = JPATH_SITE . '/cache/' . Constants::JS_CACHE_DIR;
      if (!file_exists($cacheFolder)) {
         \mkdir($cacheFolder);
      }
      $cacheFile = $cacheFolder . '/' . $cache_key . '.js';
      if (file_exists($cacheFile)) {
         return file_get_contents($cacheFile);
      } else {
         $minifier = new \MatthiasMullie\Minify\JS();
         $js = [];
         foreach ($files as $file) {
            $js[] = file_get_contents($file);
         }
         $minifier->add(implode("", $js));
         $js = $minifier->minify();
         file_put_contents($cacheFile, $js);
         return $js;
      }
   }

   public static function getMediaVersion()
   {
      $jversion = new \JVersion;
      return md5(JDB_MEDIA_VERSION . $jversion->getMediaVersion());
   }

   public static function renderGlobalScss()
   {
      $document = \JFactory::getDocument();
      $variables = Helper::getGlobalVariables();
      $name = serialize(Builder::getSettings()->toString()) . JDB_MEDIA_VERSION;

      if (file_exists(JPATH_SITE . '/media/jdbuilder/css/jdb-' . md5($name) . '.min.css')) {
         goto css;
      }

      self::clearGlobalCSS();

      $scss = new Compiler();
      $scss->setImportPaths(JPATH_SITE . '/media/jdbuilder/scss');
      $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
      $scss->setVariables($variables);
      $content = $scss->compile('@import "bootstrap.scss";');
      file_put_contents(JPATH_SITE . '/media/jdbuilder/css/jdb-' . md5($name) . '.min.css', $content);

      css: $document->addStylesheet(\JURI::root() . 'media/jdbuilder/css/jdb-' . md5($name) . '.min.css', ['version' => self::getMediaVersion()]);
   }

   public static function renderGlobalTypography()
   {
      $params = Builder::getSettings();
      foreach (['Text', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'] as $type) {
         if ($type === 'Text') {
            $typeStyle = new ElementStyle('.jdbuilder');
            $linkStyle = new ElementStyle('.jdbuilder a');
            $linkHoverStyle = new ElementStyle('.jdbuilder a:hover');
         } else {
            $typeStyle = new ElementStyle('.jdbuilder ' . strtolower($type));
            $linkStyle = new ElementStyle('.jdbuilder ' . strtolower($type) . ' a');
            $linkHoverStyle = new ElementStyle('.jdbuilder '  . strtolower($type) . ' a:hover');
         }

         $color = $params->get('global' . $type . 'Color', '');
         if ($color !== '') {
            $typeStyle->addCss('color', $color);
         }

         $linkColor = $params->get('global' . $type . 'LinkColor', '');
         if ($linkColor !== '') {
            $linkStyle->addCss('color', $linkColor);
         }

         $linkHoverColor = $params->get('global' . $type . 'LinkHoverColor', '');
         if ($linkHoverColor !== '') {
            $linkHoverStyle->addCss('color', $linkHoverColor);
         }

         $typography = $params->get('global' . $type . 'Typography', null);
         if (!empty($typography)) {
            foreach (self::$devices as $deviceKey => $device) {
               if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
                  $typeStyle->addStyle(self::typographyValue($typography->{$deviceKey}), $device);
               }
            }
         }

         $typeStyle->render();
         $linkStyle->render();
         $linkHoverStyle->render();
      }
   }

   public static function getGlobalVariables()
   {
      $globalSettings = Builder::getSettings();
      $variables = [];

      foreach (['primary', 'secondary', 'success', 'info', 'warning', 'danger'] as $color) {
         $value = $globalSettings->get('global' . \ucfirst($color), '');
         if (!empty($value)) {
            $variables[$color] = $value;
         }
      }

      $lightboxTextColor = $globalSettings->get('lightboxTextColor', '');
      if (!empty($lightboxTextColor)) {
         $variables['lightbox-text'] = $lightboxTextColor;
      }

      $lightboxBackgroundColor = $globalSettings->get('lightboxBackgroundColor', '');
      if (!empty($lightboxBackgroundColor)) {
         $variables['lightbox-background'] = $lightboxBackgroundColor;
      }

      $lightboxIconColor = $globalSettings->get('lightboxIconColor', '');
      if (!empty($lightboxIconColor)) {
         $variables['lightbox-icon-color'] = $lightboxIconColor;
      }

      $lightboxIconHoverColor = $globalSettings->get('lightboxIconHoverColor', '');
      if (!empty($lightboxIconHoverColor)) {
         $variables['lightbox-icon-hover-color'] = $lightboxIconHoverColor;
      }

      $lightboxFullscreen = $globalSettings->get('lightboxFullscreen', true);
      $variables['lightbox-fullscreen-display'] = filter_var($lightboxFullscreen, FILTER_VALIDATE_BOOLEAN) ? 'flex' : 'none';

      $lightboxCounter = $globalSettings->get('lightboxCounter', true);
      $variables['lightbox-counter-display'] = filter_var($lightboxCounter, FILTER_VALIDATE_BOOLEAN) ? 'flex' : 'none';

      $lightboxIconSize = $globalSettings->get('lightboxIconSize', null);
      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($lightboxIconSize->{$deviceKey}) && Helper::checkSliderValue($lightboxIconSize->{$deviceKey})) {
            $variables['lightbox-icon-size-' . $deviceKey] = $lightboxIconSize->{$deviceKey}->value . 'px';
         }
      }

      $lightboxNavigationIconSize = $globalSettings->get('lightboxNavigationIconSize', null);
      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($lightboxNavigationIconSize->{$deviceKey}) && Helper::checkSliderValue($lightboxNavigationIconSize->{$deviceKey})) {
            $variables['lightbox-navigation-size-' . $deviceKey] = $lightboxNavigationIconSize->{$deviceKey}->value . 'px';
         }
      }
      return $variables;
   }

   public static function clearGlobalCSS()
   {

      $dir = JPATH_SITE . '/media/jdbuilder/css';
      $prefix = 'jdb';

      $styles = preg_grep('~^' . $prefix . '-.*\.(css)$~', scandir($dir));
      foreach ($styles as $style) {
         unlink($dir . '/' . $style);
      }

      $version = new \JVersion;
      $version->refreshMediaVersion();

      return true;
   }

   public static function mediaValue($value)
   {
      $type = substr($value, 0, 5);
      $return = "";
      switch ($type) {
         case 'link:':
            $return = substr($value, 5);
            break;
         default:
            $return = \JURI::root() . 'images/' . $value;
            break;
      }
      return $return;
   }

   public static function videoValue($value)
   {
      $params = new \JRegistry();
      $type = substr($value, 0, 3);
      switch ($type) {
         case 'yt:':
            $link = substr($value, 3);
            return self::getYoutubeVideoByLink($params, $link, true, false, false, true);
            break;
         case 'vm:':
            $link = substr($value, 3);
            return self::getVimeoVideoByLink($params, $link, true, false, false, true);
            break;
         case 'lk:':
            $link = substr($value, 3);
            return self::getVideoByLink($params, $link, true, false, false, true);
            break;
         default:
            $link = \JURI::root() . 'images/' . $value;
            return self::getVideoByLink($params, $link, true, false, false, true);
            break;
      }
   }

   public static function fontFamilyValue($value)
   {
      $type = substr($value, 0, 2);
      $return = "";
      switch ($type) {
         case 'g~':
            $value = substr($value, 2);
            $font = explode(":", $value);
            Builder::addStylesheet("https://fonts.googleapis.com/css?family=" . $font[0] . ':' . $font[1]);
            $font = $font[0];
            if (preg_match('~[0-9]+~', $font)) {
               $font = "'{$font}'";
            }
            $return = str_replace('+', ' ', $font);
            break;
         case 'c~':
            $value = substr($value, 2);
            $customFonts = \json_decode(file_get_contents(JPATH_PLUGINS . '/system/jdbuilder/fonts/fonts.json'), true);
            $value = $customFonts[$value];
            $urls = [];
            foreach ($value['files'] as $file) {
               $urls[] = 'url(' . $file . ')';
            }
            Builder::addCustomStyle('@font-face {font-family: ' . $value['name'] . ';src: ' . implode(', ', $urls) . ';}');
            $return = $value['name'];
            break;
         default:
            $return = substr($value, 2);
            break;
      }
      return $return;
   }

   public static function typographyValue($value = null)
   {
      $return = [];
      if (!empty($value)) {
         if (isset($value->family) && !empty($value->family)) {
            $family = self::fontFamilyValue($value->family);
            $return[] = "font-family:{$family}";
         }
         if (isset($value->size) && is_numeric($value->size)) {
            $return[] = "font-size:{$value->size}{$value->sizeUnit}";
         }
         if (isset($value->alignment) && !empty($value->alignment)) {
            $return[] = "text-align:{$value->alignment}";
         }
         if (isset($value->weight) && !empty($value->weight)) {
            $return[] = "font-weight:{$value->weight}";
         }
         if (isset($value->transform) && !empty($value->transform)) {
            $return[] = "text-transform:{$value->transform}";
         }
         if (isset($value->style) && !empty($value->style)) {
            $return[] = "font-style:{$value->style}";
         }
         if (isset($value->decoration) && !empty($value->decoration)) {
            $return[] = "text-decoration:{$value->decoration}";
         }
         if (isset($value->lineHeight) && is_numeric($value->lineHeight)) {
            $return[] = "line-height:{$value->lineHeight}{$value->lineHeightUnit}";
         }
         if (isset($value->letterSpacing) && is_numeric($value->letterSpacing)) {
            $return[] = "letter-spacing:{$value->letterSpacing}{$value->letterSpacingUnit}";
         }
      }
      return implode(";", $return);
   }

   public static function spacingValue($value = null, $property = "padding", $default = [])
   {
      $return = [];
      $values = [];
      if (!empty($value) && isset($value->unit)) {
         $unit = $value->unit;
         if ($value->lock && is_numeric($value->top)) {
            foreach (['top', 'right', 'bottom', 'left'] as $position) {
               $return[$position] = self::getPropertySubset($property, $position) . ":{$value->top}{$unit}";
               $values[$position] = "{$value->top}{$unit}";
            }
         } else {
            foreach (['top', 'right', 'bottom', 'left'] as $position) {
               $pvalue = $value->{$position};
               if (is_numeric($pvalue)) {
                  $return[$position] = self::getPropertySubset($property, $position) . ":{$pvalue}{$unit}";
                  $values[$position] = "{$pvalue}{$unit}";
               }
            }
         }
      }

      if (!isset($default['unit'])) {
         $default['unit'] = 'px';
      }

      foreach (array_keys($default) as $position) {
         if ($position == "unit") {
            continue;
         }
         if (!isset($return[$position])) {
            $return[$position] = self::getPropertySubset($property, $position) . ":{$default[$position]}{$default['unit']}";
            $values[$position] = "{$default[$position]}{$default['unit']}";
         }
      }


      if (count(array_keys($values)) === 4) {
         $return = [];
         $return[] = self::getPropertySet($property) . ':' . implode(' ', $values);
      }

      return implode(";", $return);
   }

   public static function checkSliderValue($var, $name = '')
   {


      if (empty($var) || !isset($var->value) || !isset($var->unit)) {
         return FALSE;
      }

      if (is_object($var->value) || is_array($var->value)) {
         return FALSE;
      }

      if ($var->value === NULL || $var->value === '') {
         return FALSE;
      }

      return TRUE;
   }

   public static function getPropertySubset($property, $position)
   {
      switch ($property) {
         case "radius":
            switch ($position) {
               case "top":
                  return 'border-top-left-radius';
                  break;
               case "left":
                  return 'border-bottom-left-radius';
                  break;
               case "right":
                  return 'border-top-right-radius';
                  break;
               case "bottom":
                  return 'border-bottom-right-radius';
                  break;
            }
            break;
         case "border":
            return $property . '-' . $position . '-width';
            break;
         default:
            return $property . '-' . $position;
            break;
      }
   }

   public static function getPropertySet($property)
   {
      switch ($property) {
         case "radius":
            return "border-radius";
            break;
         case "border":
            return "border-width";
            break;
         default:
            return $property;
            break;
      }
   }

   public static function isValidJSON($string)
   {
      $data = json_decode($string);
      return (json_last_error() == JSON_ERROR_NONE) ? TRUE : FALSE;
   }

   public static function getCaretValue($value)
   {
      if (empty($value) || $value == 'none' || !in_array($value, ['plus', 'triangle', 'chevron', 'arrow'])) {
         return '';
      }

      $onIcon = '';
      $offIcon = '';
      switch ($value) {
         case "plus":
            $onIcon = 'fas fa-minus';
            $offIcon = 'fas fa-plus';
            break;
         case "triangle":
            $onIcon = 'fas fa-caret-up';
            $offIcon = 'fas fa-caret-down';
            break;
         case "chevron":
            $onIcon = 'fas fa-chevron-up';
            $offIcon = 'fas fa-chevron-down';
            break;
         case "arrow":
            $onIcon = 'fas fa-arrow-up';
            $offIcon = 'fas fa-arrow-down';
            break;
      }

      Builder::loadFontLibraryByIcon($onIcon);

      return '<span class="jdb-caret"><span><i class="jdb-caret-on ' . $onIcon . '"></i><i class="jdb-caret-off ' . $offIcon . '"></i></span></span>';
   }

   public static function joomlaVariables($body)
   {
      $array = ['siteurl', 'sitename'];
      foreach ($array as $var) {
         $body = self::replaceJoomlaVariable($var, $body);
      }
      return $body;
   }

   public static function replaceJoomlaVariable($var, $body)
   {
      $with = "";
      switch ($var) {
         case "siteurl":
            $with = \JURI::root();
            break;
         case "sitename":
            $config = \JFactory::getConfig();
            $with = $config->get('sitename');
            break;
      }

      $body = str_replace('{' . $var . '}', $with, $body);
      return $body;
   }

   public static function renderHTML($html, $indexMode = false)
   {
      $html = self::joomlaVariables($html);
      if (!$indexMode) {
         $html = self::renderModules($html);
      }
      return $html;
   }

   public static function renderModules($html)
   {
      preg_match_all('#\[jmodule (.*?)\]#', $html, $matches);
      foreach ($matches[1] as $index => $string) {
         $shortcode = $matches[0][$index];

         preg_match('#position=\"(.*?)\"#', $string, $positionMatch);
         preg_match('#id=\"(.*?)\"#', $string, $idMatch);
         // preg_match('#name=\"(.*?)\"#', $string, $nameMatch);
         preg_match('#style=\"(.*?)\"#', $string, $styleMatch);

         $style = 'none';
         if (!empty($styleMatch)) {
            $style = $styleMatch[1];
         }

         if (!empty($idMatch)) {
            $html = str_replace($shortcode, Builder::renderModuleByID($idMatch[1], $style), $html);
         } else if (!empty($positionMatch)) {
            $html = str_replace($shortcode, Builder::renderModulePosition($positionMatch[1], $style), $html);
         }
         /* else if (!empty($nameMatch)) {
            $html = str_replace($shortcode, self::loadModuleByName($nameMatch[1], $style), $html);
         } */
      }
      return $html;
   }

   public static function getFavouriteTemplates()
   {
      $db = \JFactory::getDbo();
      $query = "SELECT `template_id` FROM `#__jdbuilder_favourites`";
      $db->setQuery($query);
      $favourites = (array) $db->loadObjectList();
      if (empty($favourites)) {
         return [];
      }
      return array_column($favourites, 'template_id');
   }

   public static function getPageItemIdByLink($link)
   {
      $db = \JFactory::getDbo();
      $query = "SELECT `id` FROM `#__menu` WHERE `link`='{$link}' AND `published`=1";
      $db->setQuery($query);
      $result = $db->loadObject();
      if (empty($result)) {
         return 0;
      }
      return $result->id;
   }

   public static function getDir($dir, $extension = null, &$results = array())
   {
      $files = scandir($dir);

      foreach ($files as $key => $value) {
         $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
         if (!is_dir($path)) {
            $pathinfo = pathinfo($path);
            if ($extension !== null && $pathinfo['extension'] == $extension) {
               $include_path = str_replace(JPATH_THEMES, '', $path);
               $component_name = str_replace('.min', '', $pathinfo['filename']);

               $pdir = pathinfo($path)['dirname'];
               $prefix = [];
               while (basename($pdir) != 'partials') {
                  $prefix[] = basename($pdir);
                  $pdir = pathinfo($pdir)['dirname'];
               }
               $prefix[] = $component_name;
               $results[implode('-', $prefix)] = ['component_name' => implode('-', $prefix), 'name' => $pathinfo['basename'], 'path' => $include_path, 'basepath' => $path];
            } elseif ($extension === null) {
               $include_path = str_replace(JPATH_THEMES, '', $path);
               $results[] = ['name' => $pathinfo['basename'], 'path' => $include_path];
            }
         } else if ($value != "." && $value != "..") {
            self::getDir($path, $extension, $results);
         }
      }

      return $results;
   }

   public static function getElementPartials($dir)
   {
      if (!file_exists($dir . '/tmpl/partials')) {
         return '';
      }
      $element = basename($dir);
      $partials = self::getDir($dir . '/tmpl/partials', 'html');

      $return = '';
      foreach ($partials as $partial) {
         $return .= '<script type="x-tmpl-mustache" id="' . $element . '-' . $partial['component_name'] . '">' . trim(file_get_contents($partial['path'])) . '</script>';
      }
      return $return;
   }

   public static function loadBuilderLanguage()
   {
      $lang = \JFactory::getLanguage();
      $tag = $lang->getTag();
      $path = JPATH_PLUGINS . "/system/jdbuilder/language/{$tag}/{$tag}.jdb.ini";
      if (!file_exists($path)) {
         $path = JPATH_PLUGINS . "/system/jdbuilder/language/en-GB/en-GB.jdb.ini";
      }
      $strings = self::parseIniFile($path);
      if ($lang->getDebug()) {
         foreach ($strings as &$string) {
            $string = '**' . $string . '**';
         }
      }

      $document = \JFactory::getDocument();
      $document->addScriptDeclaration('_JDB.LANG = ' . \json_encode($strings) . ';');
   }

   public static function parseIniFile($fileName, $debug = false)
   {
      // Check if file exists.
      if (!file_exists($fileName)) {
         return array();
      }

      // @deprecated 3.9.0 Usage of "_QQ_" is deprecated. Use escaped double quotes (\") instead.
      if (!defined('_QQ_')) {
         /**
          * Defines a placeholder for a double quote character (") in a language file
          *
          * @var    string
          * @since  1.6
          * @deprecated  4.0 Use escaped double quotes (\") instead.
          */
         define('_QQ_', '"');
      }

      // Capture hidden PHP errors from the parsing.
      if ($debug === true) {
         // See https://secure.php.net/manual/en/reserved.variables.phperrormsg.php
         $php_errormsg = null;

         $trackErrors = ini_get('track_errors');
         ini_set('track_errors', true);
      }

      // This was required for https://github.com/joomla/joomla-cms/issues/17198 but not sure what server setup
      // issue it is solving
      $disabledFunctions = explode(',', ini_get('disable_functions'));
      $isParseIniFileDisabled = in_array('parse_ini_file', array_map('trim', $disabledFunctions));

      if (!function_exists('parse_ini_file') || $isParseIniFileDisabled) {
         $contents = file_get_contents($fileName);
         $contents = str_replace('_QQ_', '"\""', $contents);
         $strings = @parse_ini_string($contents);
      } else {
         $strings = @parse_ini_file($fileName);
      }

      // Restore error tracking to what it was before.
      if ($debug === true) {
         ini_set('track_errors', $trackErrors);
      }

      return is_array($strings) ? $strings : array();
   }

   public static function JRouteLink($client, $url, $xhtml = true, $ssl = null)
   {
      // If we cannot process this $url exit early.
      if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0)) {
         return $url;
      }

      // Get the router instance, only attempt when a client name is given.
      if ($client && !isset(self::$_router[$client])) {
         $app = \JFactory::getApplication();
         self::$_router[$client] = $app->getRouter($client);
      }

      // Make sure that we have our router
      if (!isset(self::$_router[$client])) {
         throw new \RuntimeException(\JText::sprintf('JLIB_APPLICATION_ERROR_ROUTER_LOAD', $client), 500);
      }

      // Build route.
      $uri = self::$_router[$client]->build($url);
      $scheme = array('path', 'query', 'fragment');

      /*
       * Get the secure/unsecure URLs.
       *
       * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
       * https and need to set our secure URL to the current request URL, if not, and the scheme is
       * 'http', then we need to do a quick string manipulation to switch schemes.
       */
      if ((int) $ssl || $uri->isSsl()) {
         static $host_port;

         if (!is_array($host_port)) {
            $uri2 = \JURI::getInstance();
            $host_port = array($uri2->getHost(), $uri2->getPort());
         }

         // Determine which scheme we want.
         $uri->setScheme(((int) $ssl === 1 || $uri->isSsl()) ? 'https' : 'http');
         $uri->setHost($host_port[0]);
         $uri->setPort($host_port[1]);
         $scheme = array_merge($scheme, array('host', 'port', 'scheme'));
      }

      $url = $uri->toString($scheme);

      // Replace spaces.
      $url = preg_replace('/\s/u', '%20', $url);

      if ($xhtml) {
         $url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');
      }

      return $url;
   }

   public static function applyBackgroundValue($elementStyle, $params, $key = '')
   {
      if (empty($key)) {
         return;
      }
      $background = $params->get($key . 'Type', 'none');
      switch ($background) {
         case "color":
            $backgroundColor = $params->get($key . 'Color', '');
            if ($backgroundColor != '') {
               $elementStyle->addCss('background-color', $backgroundColor);
            }
            break;
         case "image":
            $backgroundColor = $params->get($key . 'Color', '');
            if ($backgroundColor != '') {
               $elementStyle->addCss('background-color', $backgroundColor);
            }
            $backgroundImage = $params->get($key . 'Image', '');
            if ($backgroundImage != '') {
               $elementStyle->addCss('background-image', 'url(' . Helper::mediaValue($backgroundImage) . ')');
               $backgroundRepeat = $params->get($key . 'Repeat', '');
               if ($backgroundRepeat != '') {
                  $elementStyle->addCss('background-repeat', $backgroundRepeat);
               }
               $backgroundSize = $params->get($key . 'Size', '');
               if ($backgroundSize != '') {
                  $elementStyle->addCss('background-size', $backgroundSize);
               }
               $backgroundAttachment = $params->get($key . 'Attachment', '');
               if ($backgroundAttachment != '') {
                  $elementStyle->addCss('background-attachment', $backgroundAttachment);
               }
               $backgroundPosition = $params->get($key . 'Position', '');
               if ($backgroundPosition != '') {
                  $elementStyle->addCss('background-position', $backgroundPosition);
               }
            }
            break;
         case 'gradient':
            $backgroundGradient = $params->get($key . 'Gradient', '');
            if ($backgroundGradient != '') {
               $elementStyle->addCss('background-image', $backgroundGradient);
            }
            break;
      }
   }

   public static function applyBorderValue($elementStyle, $params, $key = '')
   {
      if (empty($key)) {
         return;
      }
      $elementBorderStyle = $params->get($key . 'Style', '');
      if (!empty($elementBorderStyle)) {
         $elementStyle->addCss('border-style', $elementBorderStyle);
         $elementBorderWidth = $params->get($key . 'Width', null);
         if (!empty($elementBorderWidth) && $elementBorderStyle != 'none') {

            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
               if (isset($elementBorderWidth->{$deviceKey}) && !empty($elementBorderWidth->{$deviceKey})) {

                  $css = \JDPageBuilder\Helper::spacingValue($elementBorderWidth->{$deviceKey}, "border");
                  if (!empty($css)) {
                     $elementStyle->addStyle($css, $device);
                  }
               }
            }

            $elementBorderColor = $params->get($key . 'Color', '');
            if (!empty($elementBorderColor)) {
               $elementStyle->addCss('border-color', $elementBorderColor);
            }
         }
      }
      $elementBorderRadius = $params->get($key . 'Radius', null);
      if (!empty($elementBorderRadius)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($elementBorderRadius->{$deviceKey}) && !empty($elementBorderRadius->{$deviceKey})) {

               $css = \JDPageBuilder\Helper::spacingValue($elementBorderRadius->{$deviceKey}, "radius");
               if (!empty($css)) {
                  $elementStyle->addStyle($css, $device);
               }
            }
         }
      }
      $elementBoxShadow = $params->get($key . 'Shadow', '');
      if (!empty($elementBoxShadow)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($elementBoxShadow->{$deviceKey}) && !empty($elementBoxShadow->{$deviceKey})) {
               $elementStyle->addCss('box-shadow', $elementBoxShadow->{$deviceKey}, $device);
            }
         }
      }
   }

   public static function firstWord($html)
   {
      $string = strip_tags($html);
      return explode(" ", $string)[0];
   }
   public static function firstLetter($str)
   {
      return substr($str, 0, 1);
   }

   public static function customCSS($css, $parent, $device)
   {
      $parsed = self::parseCss($css);
      foreach ($parsed as $selector => $rules) {
         if ($selector === 'self' && $parent !== null) {
            $parent->addStyle($rules, $device);
         } else {
            $child = new ElementStyle($selector);
            if ($parent != null) {
               $parent->addChildStyle($child);
            }
            $child->addStyle($rules, $device);
            if ($parent == null) {
               $child->render();
            }
         }
      }
   }

   public static function parseCss($css)
   {
      if (empty($css) || !is_string($css)) {
         return [];
      }
      $results = array();

      $oCssParser = new Parser($css);
      $oCssDocument = $oCssParser->parse();
      foreach ($oCssDocument->getAllDeclarationBlocks() as $oBlock) {
         $selectors = [];
         foreach ($oBlock->getSelectors() as $oSelector) {
            $selectors[] = $oSelector->getSelector();
         }
         $css = [];
         foreach ($oBlock->getRulesAssoc() as $rule) {
            $oFormat = OutputFormat::create()->indentWithSpaces(4)->setSpaceBetweenRules("\n");
            $css[] = $rule->render($oFormat);
         }
         $results[implode(', ', $selectors)] = implode('', $css);
      }
      return $results;
   }

   public static function jdApiRequest($method, $hook, $data)
   {
      $method = strtoupper($method);
      $url = "https://api.joomdev.com/api/" . $hook;
      $client = new CurlHttpClient();
      $dataType = $method == 'POST' ? 'body' : 'query';
      $response = $client->request($method, $url, [
         'verify_peer' => false,
         'verify_host' => false,
         'headers' => [
            'Accept' => '*/*',
            'Cache-Control' => 'no-cache',
         ],
         $dataType => $data
      ]);
      $respond = $response->getContent();
      $output = \json_decode($respond, true);
      if ($output['status'] == 'error') {
         throw new \Exception($output['message'], $output['code']);
      }

      $return = ['data' => null, 'messages' => []];
      if (is_object($output)) {
         $output = (array) $output;
      }
      if (isset($output['data'])) {
         $return['data'] = $output['data'];
      }
      if (isset($output['messages'])) {
         $return['messages'] = $output['messages'];
      }
      return $return;
   }

   public static function chopString($string, $limit = 100)
   {
      $string = strip_tags($string);
      $subtring = substr($string, 0, $limit);
      if (\strlen($subtring) != \strlen($string)) {
         $subtring .= '...';
      }
      return $subtring;
   }

   public static function renderMustacheTemplate($template, $data = [])
   {
      $m = new Mustache_Engine();
      return $m->render($template, $data);
   }

   public static function renderMustacheTemplateByFile($file, $data = [])
   {
      if (!file_exists($file)) {
         return '';
      }
      $m = new Mustache_Engine();
      return $m->render(file_get_contents($file), $data);
   }

   public static function getElementByAjaxID($ajaxID)
   {
      $object = new \stdClass();
      $object->id = null;
      $object->type = '';
      $object->params = new \JRegistry();
      try {
         @list($id, $type, $eid) = explode('$', $ajaxID);

         if (empty($id) || empty($type) || empty($eid)) {
            throw new \Exception('Element data not found.');
         }

         if (!in_array($type, ['page', 'article', 'module'])) {
            throw new \Exception('Element data not found.');
         }

         $db = \JFactory::getDbo();
         switch ($type) {
            case 'page':
               $query = "SELECT * FROM `#__jdbuilder_layouts` JOIN `#__jdbuilder_pages` ON `#__jdbuilder_pages`.`layout_id`=`#__jdbuilder_layouts`.`id` WHERE `#__jdbuilder_pages`.`id`='{$id}' AND `#__jdbuilder_layouts`.`layout` LIKE '%{$eid}%'";
               break;
            case 'article':
               $db->setQuery("SELECT * FROM `#__content` WHERE `id`='{$id}'");
               $article = $db->loadObject();
               if (empty($article)) {
                  throw new \Exception('Element data not found.');
               }
               $attribs = new \JRegistry();
               $attribs->loadString($article->attribs);
               $lid = $attribs->get('jdbuilder_layout_id', 0);
               if (empty($lid)) {
                  throw new \Exception('Element data not found.');
               }
               $query = "SELECT * FROM `#__jdbuilder_layouts` WHERE `id`='{$lid}' AND `layout` LIKE '%{$eid}%'";
               break;
            case 'module':
               $db->setQuery("SELECT * FROM `#__modules` WHERE `module`='mod_jdbuilder' AND `id`='{$id}'");
               $module = $db->loadObject();
               if (empty($module)) {
                  throw new \Exception('Element data not found.');
               }
               $attribs = new \JRegistry();
               $attribs->loadString($module->params);
               $lid = $attribs->get('jdbuilder_layout', 0);
               if (empty($lid)) {
                  throw new \Exception('Element data not found.');
               }
               $query = "SELECT * FROM `#__jdbuilder_layouts` WHERE `id`='{$lid}' AND `layout` LIKE '%{$eid}%'";
               break;
         }
         $db->setQuery($query);
         $result = $db->loadObject();

         if (empty($result)) {
            throw new \Exception('Element data not found.');
         }

         $layout = new Layout($result);
         foreach ($layout->sections as $section) {
            foreach ($section->rows as $row) {
               foreach ($row->columns as $column) {
                  foreach ($column->elements as $element) {
                     if ($element->id === $eid) {
                        $object->params = $element->params;
                        $object->type = $element->type;
                        $object->id = $element->id;
                        break 4;
                     }
                     if ($element->type == 'inner-row') {
                        foreach ($element->columns as $icolumn) {
                           foreach ($icolumn->elements as $ielement) {
                              if ($ielement->id === $eid) {
                                 $object->params = $ielement->params;
                                 $object->type = $ielement->type;
                                 $object->id = $ielement->id;
                                 break 6;
                              }
                           }
                        }
                     }
                  }
               }
            }
         }
      } catch (\Exception $e) {
      }
      return $object;
   }

   public static function sendMail($from = '', $to = [], $subject = '', $body = '', $attachments = [], $fromName = '', $replyTo = '', $cc = [], $bcc = [], $html = true)
   {
      $mailer = \JFactory::getMailer();
      $config = \JFactory::getConfig();

      $sender = [];
      $sender[] = !empty($from) ? $from : $config->get('mailfrom');
      $sender[] = !empty($fromName) ? $fromName : $config->get('fromname');
      $mailer->setSender($sender);

      $user = \JFactory::getUser();
      $recipient = !empty($to) ? $to : $user->email;
      $mailer->addRecipient($recipient);

      if (!empty($replyTo)) {
         $mailer->addReplyTo($replyTo);
      }

      if (!empty($cc)) {
         $mailer->addCc($cc);
      }

      if (!empty($bcc)) {
         $mailer->addBcc($bcc);
      }

      $mailer->setSubject($subject);
      if ($html) {
         $mailer->isHtml(true);
         $mailer->Encoding = 'base64';
      }
      $mailer->setBody($body);

      foreach ($attachments as $attachment) {
         $mailer->addAttachment($attachment);
      }

      $send = $mailer->Send();
      self::log('mail', \json_encode($mailer));
      if ($send !== true) {
         return false;
      } else {
         return true;
      }
   }

   public static function renderButtonValue($key, $element, $text = '', $classes = [], $type = "link", $link = '#', $targetBlank = false, $nofollow = false)
   {
      $params = $element->params;
      $html = [];
      $size = $params->get($key . 'Size', '');
      $animation = $params->get($key . 'Animation', '');

      $class = [];
      $class[] = 'jdb-button';
      $class[] = 'jdb-button-' . $params->get($key . 'Type', 'primary');
      if (!empty($size)) {
         $class[] = 'jdb-button-' . $size;
      }
      $class[] = 'jdb-button-' . $key;
      foreach ($classes as $c) {
         if (!empty($c)) {
            $class[] = str_replace('*', $key, $c);
         }
      }

      $iconHTML = '';
      $buttonIcon = $params->get($key . 'Icon', '');
      $iconPosition = $params->get($key . 'IconPosition', 'right');
      if (!empty($buttonIcon)) {
         $iconAnimation = $params->get($key . 'IconAnimation', '');
         if (!empty($iconAnimation)) {
            $class[] = 'jdb-hover-' . $iconAnimation;
         }
         \JDPageBuilder\Builder::loadFontLibraryByIcon($buttonIcon);
         $iconHTML = '<span class="jdb-button-icon jdb-hover-icon ' . $buttonIcon . ' jdb-button-icon-' . $iconPosition . '"></span>';
      }

      $html[] = '<div class="jdb-button-container jdb-button-container-' . $key . '">';
      $html[] = '<div class="jdb-button-wrapper">';
      $html[] = '<div class="' . implode(' ', $class) . '">';
      if ($type == 'link') {
         $html[] = '<a title="' . $text . '" href="' . $link . '" class="jdb-button-link' . (!empty($animation) ? ' jdb-hover-' . $animation : '') . '"' . ($targetBlank ? ' target="_blank"' : '') . ($nofollow ? ' rel="nofollow"' : '') . '>';
      } else {
         $html[] = '<button type="' . $type . '" title="' . $text . '" class="jdb-button-link' . (!empty($animation) ? ' jdb-hover-' . $animation : '') . '">';
      }

      if ($iconPosition == 'left') {
         $html[] = $iconHTML;
      }
      $html[] = $text;
      if ($iconPosition == 'right') {
         $html[] = $iconHTML;
      }

      if ($type == 'link') {
         $html[] = '</a>';
      } else {
         $html[] = '</button>';
      }
      $html[] = '</div>';
      $html[] = '</div>';
      $html[] = '</div>';
      self::applyButtonValue($key, $element);
      return implode('', $html);
   }

   public static function applyButtonValue($btnKey, $element)
   {
      $buttonWrapperStyle = new ElementStyle(".jdb-button-container-" . $btnKey . " .jdb-button-wrapper");
      $buttonStyle = new ElementStyle(".jdb-button-" . $btnKey . " >  .jdb-button-link");
      $buttonHoverStyle = new ElementStyle(".jdb-button-" . $btnKey . " >  .jdb-button-link:hover");

      $element->addChildrenStyle([$buttonWrapperStyle, $buttonStyle, $buttonHoverStyle]);

      // button alignment
      $alignment = $element->params->get($btnKey . 'Alignment', null);
      if (!empty($alignment)) {
         foreach (self::$devices as $deviceKey => $device) {
            if (isset($alignment->{$deviceKey}) && !empty($alignment->{$deviceKey})) {
               $align = $alignment->{$deviceKey};
               if ($align != 'block') {
                  $buttonWrapperStyle->addCss('flex', '0 0 auto', $device);
                  $buttonWrapperStyle->addCss('-ms-flex', '0 0 auto', $device);
                  $buttonWrapperStyle->addCss('width', 'auto', $device);
                  if ($align == 'center') {
                     $buttonWrapperStyle->addCss('margin-right', 'auto', $device);
                     $buttonWrapperStyle->addCss('margin-left', 'auto', $device);
                  } else if ($align == 'right') {
                     $buttonWrapperStyle->addCss('margin-right', 'initial', $device);
                     $buttonWrapperStyle->addCss('margin-left', 'auto', $device);
                  } else {
                     $buttonWrapperStyle->addCss('margin-right', 'auto', $device);
                     $buttonWrapperStyle->addCss('margin-left', 'initial', $device);
                  }
               } else {
                  $buttonWrapperStyle->addCss('flex', '0 0 100%', $device);
                  $buttonWrapperStyle->addCss('-ms-flex', '0 0 100%', $device);
                  $buttonWrapperStyle->addCss('width', '100%', $device);
                  $buttonWrapperStyle->addCss('margin-right', 'initial', $device);
                  $buttonWrapperStyle->addCss('margin-left', 'initial', $device);
               }
            }
         }
      }

      // Background
      $buttonStyle->addCss("background-color", $element->params->get($btnKey . 'Background', ''));
      $buttonHoverStyle->addCss("background-color", $element->params->get($btnKey . 'BackgroundHover', ''));

      // Text Color
      $buttonStyle->addCss("color", $element->params->get($btnKey . 'Foreground', ''));
      $buttonHoverStyle->addCss("color", $element->params->get($btnKey . 'ForegroundHover', ''));


      // Border Color
      $buttonStyle->addCss("border-color", $element->params->get($btnKey . 'BorderColor', ''));
      $buttonHoverStyle->addCss("border-color", $element->params->get($btnKey . 'BorderColorHover', ''));

      // Gradient
      $buttonStyle->addCss("background-image", $element->params->get($btnKey . 'Gradient', ''));
      $buttonHoverStyle->addCss("background-image", $element->params->get($btnKey . 'GradientHover', ''));
      if (!empty($element->params->get($btnKey . 'Gradient', '')) && empty($element->params->get($btnKey . 'GradientHover', ''))) {
         $buttonHoverStyle->addCss("background-image", 'none');
      }

      // Typography
      $typography = $element->params->get($btnKey . 'Typography', null);
      if (!empty($typography)) {
         foreach (self::$devices as $deviceKey => $device) {
            if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
               $buttonStyle->addStyle(self::typographyValue($typography->{$deviceKey}), $device);
            }
         }
      }

      // Padding
      $padding = $element->params->get($btnKey . 'Padding', null);
      if (!empty($padding)) {
         foreach (self::$devices as $deviceKey => $device) {
            if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
               $buttonStyle->addStyle(self::spacingValue($padding->{$deviceKey}, "padding"), $device);
            }
         }
      }

      // Border
      $borderType = $element->params->get($btnKey . 'BorderStyle', 'solid');
      $buttonStyle->addCss("border-style", $borderType);
      if ($borderType != 'none') {
         $borderWidth = $element->params->get($btnKey . 'BorderWidth', null);
         if ($borderWidth != null) {
            foreach (self::$devices as $deviceKey => $device) {
               if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
                  $css = self::spacingValue($borderWidth->{$deviceKey}, "border");
                  $buttonStyle->addStyle($css, $device);
               }
            }
         }
      }

      // Radius
      $borderRadius = $element->params->get($btnKey . 'BorderRadius', null);
      if (!empty($borderRadius)) {
         foreach (self::$devices as $deviceKey => $device) {
            if (isset($borderRadius->{$deviceKey}) && !empty($borderRadius->{$deviceKey})) {
               $css = self::spacingValue($borderRadius->{$deviceKey}, "radius");
               $buttonStyle->addStyle($css, $device);
            }
         }
      }

      // shadow
      $buttonStyle->addCss("box-shadow", $element->params->get($btnKey . 'BoxShadow', ''));

      // Icon
      $buttonIcon = $element->params->get($btnKey . 'Icon', '');
      $iconPosition = $element->params->get($btnKey . 'IconPosition', 'right');
      if (!empty($buttonIcon)) {
         $iconStyle = new ElementStyle(".jdb-button-" . $btnKey . " >  .jdb-button-link > .jdb-button-icon");
         $element->addChildStyle($iconStyle);
         $iconColor = $element->params->get($btnKey . 'IconColor', '');
         $iconStyle->addCss("color", $iconColor);
         $iconSpacing = $element->params->get($btnKey . 'IconSpacing', null);
         if (self::checkSliderValue($iconSpacing)) {
            if ($iconPosition == "right") {
               $iconStyle->addCss("margin-left", $iconSpacing->value . "px");
            } else {
               $iconStyle->addCss("margin-right", $iconSpacing->value . "px");
            }
         }
      }
   }

   public static function getMenuLinkByItemId($itemId)
   {
      if (empty($itemId)) {
         return '';
      }

      $app = \JFactory::getApplication();
      $menu = $app->getMenu();
      $menu_item = $menu->getItem($itemId);
      if ($menu_item->type == 'url') {
         return $menu_item->link;
      }
      return \JRoute::_('index.php?Itemid=' . $itemId);
   }

   public static function refreshObjectID(&$object, $type = '', $layoutID = 0, $sectionIndex = null, $rowIndex = null, $columnIndex = null, $elementIndex = null)
   {

      $object['params'] = self::fixResonsiveFields($object['params']);

      if (!isset($object['id']) || $object['id'] == null || $object['id'] === 0) {
         if ($object['type'] === 'inner-row') {
            $type = 'inner-row';
         }
         switch ($type) {
            case 'section':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex);
               break;
            case 'row':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex);
               break;
            case 'inner-row':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
               break;
            case 'inner-column':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
               break;
            case 'column':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex);
               break;
            case 'element':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
               break;
         }
      }
      switch ($type) {
         case 'layout':
            foreach ($object['sections'] as $sIndex => &$section) {
               self::refreshID($section, 'section', $layoutID, $sIndex);
            }
            break;
         case 'section':
            foreach ($object['rows'] as $rIndex => &$row) {
               self::refreshID($row, 'row', $layoutID, $sectionIndex, $rIndex);
            }
            break;
         case 'row':
            foreach ($object['cols'] as $cIndex => &$col) {
               self::refreshID($col, 'column', $layoutID, $sectionIndex, $rowIndex, $cIndex);
            }
            break;
         case 'inner-row':
            foreach ($object['cols'] as $icIndex => &$col) {
               self::refreshID($col, 'inner-column', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $icIndex);
            }
            break;
         case 'inner-column':
            $innerColumnIndex = $elementIndex;
            foreach ($object['elements'] as $eIndex => &$element) {
               self::refreshID($element, 'element', $sectionIndex, $rowIndex, $columnIndex, $innerColumnIndex, $eIndex);
            }
            break;
         case 'column':
            foreach ($object['elements'] as $eIndex => &$element) {
               self::refreshID($element, 'element', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $eIndex);
            }
            break;
      }
      return $object;
   }

   public static function fixResonsiveFields($params)
   {
      foreach ($params as $prop => $value) {
         if ($value !== null && is_array($value)) {
            if (isset($value['md'])) {
               if (isset($value['sm']) && is_array($value['sm']) && isset($value['sm']['md'])) {
                  $value['sm'] = $value['sm']['md'];
               }

               if (isset($value['xs']) && is_array($value['xs']) && isset($value['xs']['md'])) {
                  $value['xs'] = $value['xs']['md'];
               }
            }
         }
      }
      return $params;
   }

   public static function generateID($type = null, $layoutID = 0, $sectionIndex = null, $rowIndex = null, $columnIndex = null, $elementIndex = null)
   {
      $id = [];
      if ($type !== null) {
         $id[] = 'jd';
         if ($type == 'inner-row' || $type == 'inner-column') {
            if ($type == 'inner-row') {
               $id[] = 'ir-';
            } else {
               $id[] = 'ic-';
            }
         } else {
            $id[] = substr($type, 0, 1) . '-';
         }
         $id[] = self::random(2, 2);
      }

      if ($layoutID !== null) {
         if (!is_numeric($layoutID)) {
            $layoutIDSplit = explode('-', $layoutID);
            if (count($layoutIDSplit) > 1) {
               $id[] = $layoutIDSplit[1];
            } else {
               $id[] = $layoutID;
            }
         } else {
            $id[] = $layoutID;
         }
      }

      if ($sectionIndex !== null) {
         $id[] = $sectionIndex;
      }

      if ($rowIndex !== null) {
         $id[] = $rowIndex;
      }

      if ($columnIndex !== null) {
         $id[] = $columnIndex;
      }

      if ($elementIndex !== null) {
         $id[] = $elementIndex;
      }

      $id[] = substr(time(), 3);
      $id[] = self::random(3, 2);
      return implode('', $id);
   }

   public static function refreshID(&$object, $type = null, $layoutID = 0, $sectionIndex = null, $rowIndex = null, $columnIndex = null, $elementIndex = null)
   {
      $object['params'] = self::fixResonsiveFields($object['params']);

      if (!isset($object['id']) || $object['id'] == null || $object['id'] === 0) {
         if ($object['type'] === 'inner-row') {
            $type = 'inner-row';
         }
         switch ($type) {
            case 'section':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex);
               break;
            case 'row':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex);
               break;
            case 'inner-row':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
               break;
            case 'inner-column':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
               break;
            case 'column':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex);
               break;
            case 'element':
               $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
               break;
         }
      }
      switch ($type) {
         case 'layout':
            foreach ($object['sections'] as $sIndex => &$section) {
               self::refreshID($section, 'section', $layoutID, $sIndex);
            };
            break;
         case 'section':
            foreach ($object['rows'] as $rIndex => &$row) {
               self::refreshID($row, 'row', $layoutID, $sectionIndex, $rIndex);
            }
            break;
         case 'row':
            foreach ($object['cols'] as $cIndex => &$col) {
               self::refreshID($col, 'column', $layoutID, $sectionIndex, $rowIndex, $cIndex);
            };
            break;
         case 'inner-row':
            foreach ($object['cols'] as $icIndex => &$col) {
               self::refreshID($col, 'inner-column', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $icIndex);
            };
            break;
         case 'inner-column':
            $innerColumnIndex = $elementIndex;
            foreach ($object['elements'] as $eIndex => &$element) {
               self::refreshID($element, 'element', $sectionIndex, $rowIndex, $columnIndex, $innerColumnIndex, $eIndex);
            };
            break;
         case 'column':
            foreach ($object['elements'] as $eIndex => &$element) {
               self::refreshID($element, 'element', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $eIndex);
            };
            break;
      }
      return $object;
   }

   public static function random($start, $length)
   {
      return substr(base_convert(rand(), 10, 36), $start, $length);
   }

   public static function removeID(&$object)
   {
      if (isset($object['id'])) {
         unset($object['id']);
      }
      switch ($object['type']) {
         case 'layout':
            foreach ($object['sections'] as &$section) {
               self::removeID($section);
            }
            break;
         case 'section':
            foreach ($object['rows'] as &$row) {
               self::removeID($row);
            }
            break;
         case 'row':
            foreach ($object['cols'] as &$col) {
               self::removeID($col);
            }
            break;
         case 'column':
            foreach ($object['elements'] as &$element) {
               self::removeID($element);
            };
            break;
         case 'inner-row':
            foreach ($object['cols'] as &$col) {
               self::removeID($col);
            };
            break;
      }
   }

   public static function getSmartTags()
   {
      $smartTags = Constants::SMART_TAGS;
      $return = [];
      foreach ($smartTags as $group_title => $group) {
         $_group = ['title' => $group_title, 'items' => []];
         foreach ($group as $subgroup_title => $items) {
            $_subgroup = ['title' => $subgroup_title, 'items' => []];
            foreach ($items as $item_title => $item_tag) {
               $_subgroup['items'][] = ['title' => $item_title, 'tag' => $item_tag];
            }
            $_group['items'][] = $_subgroup;
         }
         $return[] = $_group;
      }
      return $return;
   }

   public static function getClientIp()
   {
      $ipaddress = '';
      if (getenv('HTTP_CLIENT_IP'))
         $ipaddress = getenv('HTTP_CLIENT_IP');
      else if (getenv('HTTP_X_FORWARDED_FOR'))
         $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
      else if (getenv('HTTP_X_FORWARDED'))
         $ipaddress = getenv('HTTP_X_FORWARDED');
      else if (getenv('HTTP_FORWARDED_FOR'))
         $ipaddress = getenv('HTTP_FORWARDED_FOR');
      else if (getenv('HTTP_FORWARDED'))
         $ipaddress = getenv('HTTP_FORWARDED');
      else if (getenv('REMOTE_ADDR'))
         $ipaddress = getenv('REMOTE_ADDR');
      else
         $ipaddress = 'UNKNOWN';
      return $ipaddress;
   }

   public static function uploadFile($name, $src, $size, $allowedExtensions = [], $maxFileSize = 0)
   {
      jimport('joomla.filesystem.file');
      jimport('joomla.application.component.helper');

      $fullFileName = \JFile::stripExt($name);
      $filetype = \JFile::getExt($name);
      $filename = \JFile::makeSafe($fullFileName . "_" . mt_rand(10000000, 99999999) . "." . $filetype);

      $params = \JComponentHelper::getParams('com_media');
      $allowable = array_map('trim', explode(',', $params->get('upload_extensions')));

      if (!empty($allowedExtensions)) {
         $allowable = $allowedExtensions;
      }

      if (!empty($maxFileSize) && is_numeric($maxFileSize)) {
         $maxFileSize = $maxFileSize * 1000 * 1000; // MB to Bytes
         if ($size > $maxFileSize) {
            throw new \Exception(\JText::sprintf('JDB_ERROR_LARGE_FILE_SIZE', self::formatSizeUnits($maxFileSize)));
         }
      }

      if ($filetype == '' || $filetype == false || (!in_array($filetype, $allowable))) {
         throw new \Exception(\JText::sprintf('JDB_ERROR_INVALID_EXTENSION', implode(', ', $allowable)));
      } else {
         $tmppath = JPATH_SITE . '/tmp';
         if (!file_exists($tmppath . '/_jdbuploads')) {
            mkdir($tmppath . '/_jdbuploads', 0777);
         }
         $folder = md5(time() . '-' . $filename . rand(0, 99999));
         if (!file_exists($tmppath . '/_jdbuploads/' . $folder)) {
            mkdir($tmppath . '/_jdbuploads/' . $folder, 0777);
         }
         $dest = $tmppath . '/_jdbuploads/' . $folder . '/' . $filename;

         $return = null;
         if (\JFile::upload($src, $dest)) {
            $return = $dest;
         }
         return $return;
      }
   }

   public static function curlRequest($url = '', $method = '', $data = [], $contentType = 'text/json')
   {
      $dataType = $method == 'POST' ? 'body' : 'query';
      $contentType = $method == 'POST' ? 'application/x-www-form-urlencoded' : $contentType;

      $client = new CurlHttpClient();
      $response = $client->request($method, $url, [
         'verify_peer' => false,
         'verify_host' => false,
         'headers' => [
            'Content-Type' => $contentType,
            'User-Agent' => 'JD Builder',
         ],
         $dataType => $data
      ]);
      return $response->getContent();
   }

   public static function icon($icon = '', $extra = [])
   {
      return '<span class="' . $icon . (empty($extra) ? '' : ' ' . implode(' ', $extra)) . '"></span>';
   }

   public static function formatSizeUnits($bytes)
   {
      if ($bytes >= 1000000000) {
         $bytes = number_format($bytes / 1000000000, 2) . ' GB';
      } elseif ($bytes >= 1000000) {
         $bytes = number_format($bytes / 1000000, 2) . ' MB';
      } elseif ($bytes >= 1000) {
         $bytes = number_format($bytes / 1000, 2) . ' KB';
      } elseif ($bytes > 1) {
         $bytes = $bytes . ' bytes';
      } elseif ($bytes == 1) {
         $bytes = $bytes . ' byte';
      } else {
         $bytes = '0 bytes';
      }
      return $bytes;
   }

   public static function emailExplode($string)
   {
      return preg_split("/(,|;)/", $string);
   }

   public static function str_lreplace($search, $replace, $subject)
   {
      $pos = strrpos($subject, $search);

      if ($pos !== false) {
         $subject = substr_replace($subject, $replace, $pos, strlen($search));
      }

      return $subject;
   }

   public static function globalSettings()
   {
      $db = \JFactory::getDbo();
      $query = "SELECT * FROM `#__jdbuilder_configs` WHERE `type`='global'";
      $db->setQuery($query);
      $result = $db->loadObject();

      if (empty($result)) {
         $globalConfig = '{}';
      } else {
         $globalConfig = $result->config;
      }
      $params = new \JRegistry();
      $params->loadString($globalConfig);
      return $params;
   }

   public static function animationsList()
   {
      $allAnimations = Constants::ANIMATIONS;
      $options = [];
      foreach ($allAnimations as $animationGroup => $animations) {
         $group = [];
         $group['label'] = \JText::_($animationGroup);
         $group['options'] = [];
         foreach ($animations as $value => $animation) {
            $item = [];
            $item['label'] = \JText::_($animation);
            $item['value'] = $value;
            $group['options'][] = $item;
         }
         $options[] = $group;
      }
      return ['options' => [['label' => \JText::_('JDB_NONE'), 'value' => '']], 'groups' => $options];
   }

   public static function getLightboxContent($source, $title, $caption, $altText)
   {
      $key = 'lightbox' . ucfirst($source) . 'Source';
      $settings = Builder::getSettings();
      $contentSource = $settings->get($key);
      switch ($contentSource) {
         case 'title':
            return empty($title) ? '' : $title;
         case 'caption':
            return empty($caption) ? '' : $caption;
         case 'alt':
            return empty($altText) ? '' : $altText;
      }
   }

   public static function linkValue($text, $params, $class = [], $attributes = [])
   {
      $link = $params->get('link', '');
      if ($link === '') {
         return $text;
      }
      $linkTargetBlank = $params->get('linkTargetBlank', false);
      $linkTarget = $linkTargetBlank ? ' target="_blank"' : "";

      $linkNoFollow = $params->get('linkNoFollow', false);
      $linkRel = $linkNoFollow ? ' rel="nofollow"' : "";

      return '<a href="' . $link . '" title="' . $text . '"' . $linkTarget . $linkRel . '>' . $text . '</a>';
   }
}
