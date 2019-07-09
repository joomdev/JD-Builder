<?php

namespace JDPageBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

use Leafo\ScssPhp\Compiler;
use MatthiasMullie\Minify\Minify;

class Helper {

   public static $devices = ['md' => 'desktop', 'sm' => 'tablet', 'xs' => 'mobile'];
   public static $css_cache = false;
   private static $_router = array();

   public static function classify($word) {
      return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
   }

   public static function titlecase($word) {
      return ucfirst(str_replace(['_', '-'], ' ', ucwords($word, '_-')));
   }

   public static function camelize($word) {
      return lcfirst(self::classify($word));
   }

   public static function tableize($word) {
      $tableized = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $word);
      return mb_strtolower($tableized);
   }

   public static function loadLanguage($name = "jdbuilder", $path = "") {
      if (empty($path)) {
         $path = JPATH_PLUGINS . '/system/jdbuilder';
      }
      $lang = \JFactory::getLanguage();
      $lang->load($name, $path);
      $lang->load('com_jdbuilder', JPATH_ADMINISTRATOR);
   }

   public static function jsonDecode($string, $assoc = false) {
      $array = \json_decode($string, $assoc);
      if (empty($array)) {
         return [];
      }
      return $array;
   }

   public static function getPluginParams() {
      $plugin = \JPluginHelper::getPlugin('system', 'jdbuilder');
      $params = new \JRegistry($plugin->params);
      return $params;
   }

   public static function getLayout($id) {
      $db = \JFactory::getDbo();
      $db->setQuery("SELECT * FROM `#__jdbuilder_layouts` WHERE `id`='{$id}'");
      return $db->loadObject();
   }

   public static function getPage($id) {
      $db = \JFactory::getDbo();
      $db->setQuery("SELECT * FROM `#__jdbuilder_pages` WHERE `id`='{$id}'");
      return $db->loadObject();
   }

   public static function getFieldsGroup($name, $type) {
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

   public static function getQueryString($url, $key) {
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

   public static function getVideoContent($params) {
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

   public static function getBGVideoContent($params) {
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

   public static function getYoutubeVideoByLink($params, $link, $autoplay, $mute, $loop, $controls) {

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

   public static function getVimeoVideoByLink($params, $link, $autoplay, $mute, $loop, $controls) {

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

   public static function getDailyMotionVideoByLink($params, $link, $autoplay, $mute, $loop, $controls) {

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

   public static function getVideoByLink($params, $link, $autoplay, $mute, $loop, $controls) {
      if ($params != null) {
         $start = $params->get('videoStartTime', 0);
         $end = $params->get('videoEndTime', 0);
      } else {
         $start = 0;
         $end = 0;
      }
      return '<video' . ($controls ? ' controls' : '') . '' . ($autoplay ? ' autoplay' : '') . '' . ($mute ? ' muted' : '') . '' . ($loop ? ' loop' : '') . ' ><source src="' . $link . '" type="video/mp4">Your browser does not support the video tag.</video>';
   }

   public static function minifyJS($files = []) {
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

   public static function compileSass($scss, $folder = "") {
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

   public static function renderGlobalScss() {
      $document = \JFactory::getDocument();
      $variables = Helper::getGlobalVariables();
      $name = serialize($variables);


      $document->addStylesheet(\JURI::root() . 'media/jdbuilder/css/jdb-' . md5($name) . '.min.css', ['version' => $document->getMediaVersion()]);

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

   public static function getGlobalVariables() {
      $pluginParams = self::getPluginParams();
      $variables = [];
      $variables['primary'] = $pluginParams->get('global_primary', '#007bff');
      $variables['secondary'] = $pluginParams->get('global_secondary', '#6c757d');
      $variables['success'] = $pluginParams->get('global_success', '#28a745');
      $variables['info'] = $pluginParams->get('global_info', '#17a2b8');
      $variables['warning'] = $pluginParams->get('global_warning', '#ffc107');
      $variables['danger'] = $pluginParams->get('global_danger', '#dc3545');

      //fontFamilyValue

      $global_font = $pluginParams->get('global_font', '');

      $fontfamily = [];

      if (!empty($global_font)) {
         $fontfamily[] = self::fontFamilyValue($global_font);
      }

      $global_alt_font = $pluginParams->get('global_alt_font', '');
      if (!empty($global_alt_font)) {
         $fontfamily[] = self::fontFamilyValue($global_alt_font);
      }

      if (!empty($fontfamily)) {
         $variables['font-family-sans-serif'] = implode(", ", $fontfamily);
      }
      return $variables;
   }

   public static function clearGlobalCSS() {

      $dir = JPATH_SITE . '/media/jdbuilder/css';
      $prefix = 'jdb';

      $styles = preg_grep('~^' . $prefix . '-.*\.(css)$~', scandir($dir));
      foreach ($styles as $style) {
         unlink($dir . '/' . $style);
      }

      return true;
   }

   public static function mediaValue($value) {
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

   public static function fontFamilyValue($value) {
      $type = substr($value, 0, 2);
      $return = "";
      switch ($type) {
         case 'g~':
            $value = substr($value, 2);
            $font = explode(":", $value);
            Builder::addStylesheet("https://fonts.googleapis.com/css?family=" . $font[0]);
            $return = str_replace('+', ' ', $font[0]);
            break;
         default:
            $return = substr($value, 2);
            break;
      }
      return $return;
   }

   public static function typographyValue($value = null) {
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

   public static function spacingValue($value = null, $property = "padding", $default = []) {
      $return = [];
      if (!empty($value) && isset($value->unit)) {
         $unit = $value->unit;
         if ($value->lock && is_numeric($value->top)) {
            foreach (['top', 'right', 'bottom', 'left'] as $position) {
               $return[$position] = self::getPropertySubset($property, $position) . ":{$value->top}{$unit}";
            }
         } else {
            foreach (['top', 'right', 'bottom', 'left'] as $position) {
               $pvalue = $value->{$position};
               if (is_numeric($pvalue)) {
                  $return[$position] = self::getPropertySubset($property, $position) . ":{$pvalue}{$unit}";
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
         }
      }

      return implode(";", $return);
   }

   public static function checkSliderValue($var, $name = '') {


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

   public static function getPropertySubset($property, $position) {
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

   public static function isValidJSON($string) {
      $data = json_decode($string);
      return (json_last_error() == JSON_ERROR_NONE) ? TRUE : FALSE;
   }

   public static function getCaretValue($value) {
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

   public static function joomlaVariables($body) {
      $array = ['siteurl', 'sitename'];
      foreach ($array as $var) {
         $body = self::replaceJoomlaVariable($var, $body);
      }
      return $body;
   }

   public static function replaceJoomlaVariable($var, $body) {
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

   public static function renderHTML($html) {
      $html = self::joomlaVariables($html);
      return $html;
   }

   public static function getFavouriteTemplates() {
      $db = \JFactory::getDbo();
      $query = "SELECT `template_id` FROM `#__jdbuilder_favourites`";
      $db->setQuery($query);
      $favourites = (array) $db->loadObjectList();
      if (empty($favourites)) {
         return [];
      }
      return array_column($favourites, 'template_id');
   }

   public static function getPageItemIdByLink($link) {
      $db = \JFactory::getDbo();
      $query = "SELECT `id` FROM `#__menu` WHERE `link`='{$link}'";
      $db->setQuery($query);
      $result = $db->loadObject();
      if (empty($result)) {
         return 0;
      }
      return $result->id;
   }

   public static function getDir($dir, $extension = null, &$results = array()) {
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

   public static function loadBuilderLanguage() {
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

   public static function parseIniFile($fileName, $debug = false) {
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

   public static function JRouteLink($client, $url, $xhtml = true, $ssl = null) {
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

}
