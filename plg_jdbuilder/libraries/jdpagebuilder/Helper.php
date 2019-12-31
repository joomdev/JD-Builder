<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

use JDPageBuilder\Element\ElementStyle;
use JDPageBuilder\Element\Layout;
use Leafo\ScssPhp\Compiler;
use MatthiasMullie\Minify\Minify;
use Mustache_Autoloader;
use Mustache_Engine;

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

   public static function compileSass($scss, $folder = "")
   {
      $variables = self::getGlobalVariables();

      if (!self::$css_cache) {
         $compiler = new Compiler();
         $compiler->setVariables($variables);
         $css = $compiler->compile($scss);
         return $css;
      }
      $key = md5($scss);

      $folder = empty($folder) ? "" : "/" . $folder;

      $cacheFolder = JPATH_SITE . '/cache/' . Constants::CSS_CACHE_DIR;
      if (!file_exists($cacheFolder)) {
         \mkdir($cacheFolder);
      }

      $cacheFolder = JPATH_SITE . '/cache/' . Constants::CSS_CACHE_DIR . $folder;
      if (!file_exists($cacheFolder)) {
         \mkdir($cacheFolder);
      }

      $cacheFile = $cacheFolder . '/' . $key . '.css';
      if (file_exists($cacheFile)) {
         Builder::log("getting css from `{$cacheFile}`");
         $css = file_get_contents($cacheFile);
      } else {
         Builder::log("saving css to `{$cacheFile}`");
         $compiler = new Compiler();
         $compiler->setVariables($variables);
         $css = $compiler->compile($scss);
         file_put_contents($cacheFile, $css);
      }
      return $css;
   }

   public static function renderGlobalScss()
   {
      $document = \JFactory::getDocument();
      $variables = Helper::getGlobalVariables();
      $name = serialize($variables) . JDB_MEDIA_VERSION;


      $document->addStylesheet(\JURI::root() . 'media/jdbuilder/css/jdb-' . md5($name) . '.min.css', ['version' => JDB_MEDIA_VERSION]);

      if (file_exists(JPATH_SITE . '/media/jdbuilder/css/jdb-' . md5($name) . '.min.css')) {
         return;
      }

      self::clearGlobalCSS();

      $scss = new Compiler();
      $scss->setImportPaths(JPATH_SITE . '/media/jdbuilder/scss');
      $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
      $scss->setVariables($variables);
      $content = $scss->compile('@import "bootstrap.scss";');
      file_put_contents(JPATH_SITE . '/media/jdbuilder/css/jdb-' . md5($name) . '.min.css', $content);
   }

   public static function getGlobalVariables()
   {
      // $pluginParams = self::getPluginParams();
      $buiderConfig = \JComponentHelper::getParams('com_jdbuilder');

      $variables = [];
      $variables['primary'] = $buiderConfig->get('global_primary', '#007bff');
      $variables['secondary'] = $buiderConfig->get('global_secondary', '#6c757d');
      $variables['success'] = $buiderConfig->get('global_success', '#28a745');
      $variables['info'] = $buiderConfig->get('global_info', '#17a2b8');
      $variables['warning'] = $buiderConfig->get('global_warning', '#ffc107');
      $variables['danger'] = $buiderConfig->get('global_danger', '#dc3545');

      //fontFamilyValue

      $global_font = $buiderConfig->get('global_font', '');

      $fontfamily = [];

      if (!empty($global_font)) {
         $fontfamily[] = self::fontFamilyValue($global_font);
      }

      $global_alt_font = $buiderConfig->get('global_alt_font', '');
      if (!empty($global_alt_font)) {
         $fontfamily[] = self::fontFamilyValue($global_alt_font);
      }

      if (!empty($fontfamily)) {
         $variables['font-family-sans-serif'] = implode(", ", $fontfamily);
         $variables['fontFamilySansSerif'] = implode(", ", $fontfamily);
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
            Builder::addStylesheet("https://fonts.googleapis.com/css?family=" . $font[0]);
            $return = str_replace('+', ' ', $font[0]);
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

   public static function renderHTML($html)
   {
      $html = self::joomlaVariables($html);
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
      $query = "SELECT `id` FROM `#__menu` WHERE `link`='{$link}'";
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
               $results[$component_name] = ['component_name' => $component_name, 'name' => $pathinfo['basename'], 'path' => $include_path, 'basepath' => $path];
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
      foreach ($parsed as $selector => $properties) {
         if ($selector === 'self' && $parent !== null) {
            foreach ($properties as $property => $value) {
               $parent->addCss($property, $value, $device);
            }
         } else {
            $child = new ElementStyle($selector);
            if ($parent != null) {
               $parent->addChildStyle($child);
            }
            foreach ($properties as $property => $value) {
               $child->addCss($property, $value, $device);
            }
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

      preg_match_all('/(.+?)\s?\{\s?(.+?)\s?\}/', $css, $matches);
      foreach ($matches[0] as $i => $original)
         foreach (explode(';', $matches[2][$i]) as $attr)
            if (strlen($attr) > 0) {
               list($name, $value) = explode(':', $attr);
               $results[$matches[1][$i]][trim($name)] = trim($value);
            }
      return $results;
   }

   public static function getJDArticleLayouts()
   {
      $db = \JFactory::getDbo();
      $string = '"jdbuilder_layout_enabled":"1"';
      $db->setQuery("SELECT `id` FROM `#__content` WHERE `attribs` LIKE '%{$string}%'");
      $items = $db->loadObjectList();
      $return = [];
      foreach ($items as $item) {
         $return[] = $item->id;
      }
      return $return;
   }

   public static function jdApiRequest($method, $hook, $data)
   {

      $curl = curl_init();

      $url = "https://api.joomdev.com/api/" . $hook;
      $url .= '?' . http_build_query($data);

      curl_setopt_array($curl, array(
         CURLOPT_URL => $url,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => "",
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => strtoupper($method),
         CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Host: api.joomdev.com",
            "cache-control: no-cache"
         ),
      ));

      $response = curl_exec($curl);
      curl_close($curl);

      $output = \json_decode($response, true);
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

   public static function getElementParams($elementId)
   {
      $db = \JFactory::getDbo();
      $query = "SELECT * FROM `#__jdbuilder_layouts` WHERE `layout` LIKE '%{$elementId}%'";
      $db->setQuery($query);
      $result = $db->loadObject();
      if (empty($result)) {
         throw new \Exception('Bad Request', 400);
      }
      $params = null;
      $layout = new Layout($result);
      foreach ($layout->sections as $section) {
         foreach ($section->rows as $row) {
            foreach ($row->columns as $column) {
               foreach ($column->elements as $element) {
                  if ($element->id === $elementId) {
                     $params = $element->params;
                     break 4;
                  }
               }
            }
         }
      }
      return $params;
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

   public static function renderButtonValue($key, $element, $text = '', $classes = [], $type = "link", $link = '#')
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
         $class[] = str_replace('*', $key, $c);
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

      $html[] = '<div class="jdb-button-container-' . $key . '">';
      $html[] = '<div class="' . implode(' ', $class) . '">';
      if ($type == 'link') {
         $html[] = '<a title="' . $text . '" href="' . $link . '" class="jdb-button-link' . (!empty($animation) ? ' jdb-hover-' . $animation : '') . '">';
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
      self::applyButtonValue($key, $element);
      return implode('', $html);
   }

   public static function applyButtonValue($btnKey, $element)
   {
      $buttonContainerStyle = new ElementStyle(".jdb-button-container-" . $btnKey);
      $buttonWrapperStyle = new ElementStyle(".jdb-button-" . $btnKey);
      $buttonStyle = new ElementStyle(".jdb-button-" . $btnKey . " >  .jdb-button-link");
      $buttonHoverStyle = new ElementStyle(".jdb-button-" . $btnKey . " >  .jdb-button-link:hover");

      $element->addChildrenStyle([$buttonWrapperStyle, $buttonStyle, $buttonHoverStyle, $buttonContainerStyle]);

      // button alignment
      $alignment = $element->params->get($btnKey . 'Alignment', null);
      if (!empty($alignment)) {
         foreach (self::$devices as $deviceKey => $device) {
            if (isset($alignment->{$deviceKey}) && !empty($alignment->{$deviceKey})) {
               $align = $alignment->{$deviceKey};
               if ($align != 'block') {
                  $buttonContainerStyle->addCss('text-align', $align, $device);
               } else {
                  $buttonWrapperStyle->addCss("width", "100%", $device);
                  $buttonStyle->addCss("width", "100%", $device);
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
}
