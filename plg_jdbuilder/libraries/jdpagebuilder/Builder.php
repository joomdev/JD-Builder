<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

use JDPageBuilder\Element\ElementStyle;
use JDPageBuilder\Helpers\AuditHelper;
use JDPageBuilder\Helpers\Debugger;
use JDPageBuilder\Helpers\Document;
use JDPageBuilder\Helpers\LayoutHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

abstract class Builder
{
   protected static $document = null;
   protected static $globalSettings = null;
   protected static $debugger = null;
   protected static $request = null;
   protected static $requestMethod = 'GET';
   protected static $styles = [];
   protected static $scripts = ['body' => [], 'head' => []];
   protected static $stylesheets = [];
   protected static $customCSS = [];
   protected static $javascripts = [];
   protected static $authorised = null;
   protected static $forms = [];
   protected static $cache = false;
   protected static $debug = false;
   protected static $debugmarker = null;
   protected static $logs = [];
   public static $reserved_elements = ["section", "row", "column", "element", "__page"];
   public static $css = [
      'desktop' => [],
      'tablet' => [],
      'mobile' => []
   ];

   public static function init($app = null)
   {
      global $jdlid;
      self::constants($app);
      self::$debugger = new Debugger(); // Debuuger
      self::$document = new Document(); // Document
      self::$globalSettings = Helper::globalSettings(); // Global Settings

      $option = $app->input->get('option', '');
      if (JDB_ADMIN && ($option == 'com_modules' || $option == 'com_advancedmodules')) {
         AuditHelper::auditModules();
      }
   }

   public static function getSettings()
   {
      return self::$globalSettings;
   }

   public static function getDocument(): Document
   {
      return self::$document;
   }

   public static function constants($app)
   {
      if (defined('JDBPATH_PLUGIN')) {
         return;
      }
      define('JDBPATH_PLUGIN', JPATH_PLUGINS . '/system/jdbuilder');
      define('JDBPATH_COMPONENT', JPATH_SITE . '/components/com_jdbuilder');
      define('JDBPATH_ELEMENTS', JDBPATH_PLUGIN . '/elements');
      define('JDBPATH_OPTIONS', JDBPATH_PLUGIN . '/options');
      define('JDBPATH_MEDIA', JPATH_SITE . '/media/jdbuilder');
      define('JDBURL_MEDIA', \JURI::root() . 'media/jdbuilder');

      $client = $app->isClient('administrator') ? 'administrator' : 'site';
      $childWindow = self::isChildWindow();

      define('JDB_CLIENT', $client);
      define('JDB_ADMIN', $client === 'administrator');
      define('JDB_SITE', $client === 'site');

      define('JDB_LIVE_PREVIEW', JDB_SITE && $app->input->get('jdb-live-preview', 0) && $childWindow);
      define('JDB_PREVIEW', JDB_SITE && $childWindow);
      define('JDB_FORM_TOKEN', \JSession::getFormToken());

      $config = \JFactory::getConfig();
      $dev = $config->get('jdbuilder_dev', 0);
      define('JDB_DEV', $dev);

      if (JDB_LIVE_PREVIEW || JDB_PREVIEW) {
         $config->set('offline', 0);
      }
   }

   public static function isChildWindow()
   {
      if (!isset($_SERVER['HTTP_REFERER'])) {
         return false;
      }

      $uri = \JUri::getInstance($_SERVER['HTTP_REFERER']);
      $query = $uri->getQuery(true);
      $path = $uri->getPath();
      $id = \JFactory::getApplication()->input->get('id', 0, 'INT');

      if (substr($path, -24) !== '/administrator/index.php') {
         return false;
      }

      if (!(isset($query['option']) && $query['option'] == 'com_jdbuilder')) {
         return false;
      }

      if (!(isset($query['view']) && $query['view'] == 'page')) {
         return false;
      }

      if (!(isset($query['layout']) && $query['layout'] == 'edit')) {
         return false;
      }

      if (!(isset($query['id']) && $query['id'] == $id)) {
         return false;
      }

      return true;
   }

   public static function request()
   {
      if (!static::$request) {
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            static::$request = \JFactory::getApplication()->input->post;
            static::$requestMethod = 'POST';
         } else {
            static::$request = \JFactory::getApplication()->input->get;
            static::$requestMethod = $_SERVER['REQUEST_METHOD'];
         }
      }
      return static::$request;
   }

   public static function authorised()
   {
      if (static::$authorised === null) {
         static::$authorised = \JAccess::getAuthorisedViewLevels(\JFactory::getUser()->get('id'));
      }
      return static::$authorised;
   }

   public static function debugging($status = "start")
   {
      if (!static::$debug) {
         return false;
      }
      if ($status == "start") {
         static::$debugmarker = getrusage();
      } else {
         if (static::$debugmarker === null) {
            return false;
         }
         $stop = getrusage();
         $utime = self::getRunTime($stop, static::$debugmarker, "utime");
         $stime = self::getRunTime($stop, static::$debugmarker, "stime");
         static::$debugmarker = false;
         return ['process_time' => $utime . 'ms', 'system_time' => $stime . 'ms', 'logs' => static::$logs];
      }
   }

   public static function log($message = '', $type = 'info')
   {
      static::$logs[] = ['type' => $type, 'message' => $message];
   }

   public static function getRunTime($ru, $rus, $index)
   {
      return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000)) - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
   }

   public static function addStyle($css)
   {
      static::$styles[] = $css;

      foreach (['desktop', 'mobile', 'tablet'] as $device) {
         if (isset($css[$device]) && !empty($css[$device])) {
            self::$css[$device][] = $css[$device];
         }
      }
   }

   public static function renderStyle()
   {
      $inlineScss = '';

      $stylesheets = array_reverse(self::$stylesheets);

      foreach (self::$customCSS as $custom_css) {
         $inlineScss .= $custom_css;
      }

      $document = \JFactory::getDocument();
      foreach ($stylesheets as $stylesheet) {
         $document->addStylesheet($stylesheet);
      }

      Builder::getDocument()->loadPlugins();

      foreach (self::$css as $device => $script) {
         if (!empty($script)) {
            if ($device != 'desktop') {
               if ($device == 'tablet') {
                  $inlineScss .= '@media (max-width: 991.98px) {';
               } else {
                  $inlineScss .= '@media (max-width: 767.98px) {';
               }
            }
            $script = array_reverse($script);
            $inlineScss .= implode('', $script);
            if ($device != 'desktop') {
               $inlineScss .= '}';
            }
         }
      }

      return $inlineScss;
   }

   public static function addScript($js = '', $position = 'body')
   {
      static::$scripts[$position][] = $js;
   }

   public static function addStylesheet($file)
   {
      static::$stylesheets[$file] = $file;
   }

   public static function addCustomStyle($css)
   {
      static::$customCSS[] = $css;
   }

   public static function addJavascript($file)
   {
      static::$javascripts[$file] = $file;
   }

   public static function builderArticleToggle($enabled = false, $id = 0, $lid = 0)
   {
      $layout = new \JLayoutFile('article', JDBPATH_PLUGIN . '/layouts');
      return $layout->render(['enabled' => $enabled, 'id' => $id, 'lid' => $lid]);
   }

   public static function builderArea($enabled = false, $type = 'page', $id = 0)
   {
      $layout = new \JLayoutFile('builder', JDBPATH_PLUGIN . '/layouts');
      return $layout->render(['enabled' => $enabled, 'type' => $type, 'id' => $id]);
   }

   public static function JDBBanner()
   {
      $layout = new \JLayoutFile('banner', JDBPATH_PLUGIN . '/layouts');
      return $layout->render();
   }

   public static function getElements()
   {
      $paths = [
         JDBPATH_ELEMENTS
      ];

      $elements = [];
      foreach ($paths as $path) {
         if (!file_exists($path)) {
            continue;
         }

         $elements = array_merge(self::getElementsByPath($path));
      }
      return $elements;
   }

   public static function getElementsByPath($path)
   {
      $elements = [];
      $dirs = array_filter(glob($path . '/*'), 'is_dir');

      foreach ($dirs as $dir) {

         if (in_array(strtolower(basename($dir)), self::$reserved_elements)) {
            continue;
         }

         $manifest = file_exists($dir . '/' . 'manifest.xml') ? $dir . '/' . 'manifest.xml' : $dir . '/' . basename($dir) . '.xml';
         if (file_exists($manifest)) {
            $element = self::getElementDataByManifest($manifest);
            if ($element !== false) {
               $elements[] = $element;
            }
         }
      }

      foreach (['section', 'row', 'column'] as $type) {
         $element = new \stdClass();
         $element->type = $type;
         $element->title = \ucfirst($type);
         $element->icon = "";
         $element->form = self::getForm($type);
         $elements[] = $element;
      }

      return $elements;
   }

   public static function getElementDataByManifest($manifest)
   {
      $xml = \JFactory::getXml($manifest);
      $element = new \stdClass();
      $element->type = (string) $xml->attributes()->type;
      $element->title = (string) $xml->title;
      $element->icon = (string) $xml->icon;
      $element->documentation = (string) $xml->documentation;
      $element->iconType = "icon";

      $ext = substr($element->icon, -4);
      if ($ext == '.png' || $ext == '.jpg' || $ext == '.jpeg' || $ext == '.gif' || $ext == '.ico') {
         $element->iconType = "image";
      }
      if ($ext == '.svg') {
         $element->iconType = "svg";
      }

      if ($element->iconType != "icon" && substr($element->icon, 0, 4) != "http") {
         $element->icon = \JURI::root() . $element->icon;
      }

      $disabled = (string) $xml->disabled;
      $disabled = $disabled == 'true' ? TRUE : FALSE;
      $element->disabled = $disabled;
      if ($disabled) {
         return false;
      }
      $element->form = self::getForm($element->type);
      return $element;
   }

   public static function getForm($type = null)
   {
      if ($type === null) {
         $request = self::request();
         $type = $request->get('type', '');
         if (empty($type)) {
            throw new \Exception('Invalid Element');
         }
      }
      if (isset(static::$forms[$type])) {
         return static::$forms[$type];
      }

      $xmls = self::getXMLFilesByElementType($type);

      $cacheKey = [];
      foreach ($xmls as $xml) {
         $cacheKey[] = \md5_file($xml);
      }
      $cacheKey = md5(implode("-", $cacheKey));
      $form = self::getFormCache($cacheKey);
      if ($form !== null) {
         return $form;
      }

      $form = new Form($type);
      foreach ($xmls as $xml) {
         $form->load($xml);
      }
      $formJSON = $form->get();
      self::setFormCache($cacheKey, $formJSON);
      return $formJSON;
   }

   public static function getPageForm()
   {
      $xml = JDBPATH_OPTIONS . '/page.xml';
      $form = new Form("page");
      $form->load($xml);
      $formJSON = $form->get();
      return $formJSON;
   }

   public static function getGlobalParams()
   {
      return Helper::globalSettings()->toArray();
   }

   public static function getGlobalForm()
   {
      $xml = JDBPATH_OPTIONS . '/global.xml';
      $form = new Form("global");
      $form->load($xml);
      if (file_exists(JDBPATH_OPTIONS . '/global-pro.xml')) {
         $form->load(JDBPATH_OPTIONS . '/global-pro.xml');
      }
      $formJSON = $form->get();
      return $formJSON;
   }

   public static function saveGlobalOptions()
   {
      $db = \JFactory::getDbo();
      $query = "SELECT * FROM `#__jdbuilder_configs` WHERE `type`='global'";
      $db->setQuery($query);
      $result = $db->loadObject();
      $request = self::request();
      if (empty($result)) {
         $object = new \stdClass();
         $object->id = NULL;
         $object->type = 'global';
         $object->item_id = 0;
         $object->config = \json_encode($request->get('config', [], 'ARRAY'));
         $object->created_on = time();
         $object->modified_on = time();
         $db->insertObject('#__jdbuilder_configs', $object);
      } else {
         $object = new \stdClass();
         $object->id = $result->id;
         $object->config = \json_encode($request->get('config', [], 'ARRAY'));
         $object->modified_on = time();
         $db->updateObject('#__jdbuilder_configs', $object, 'id');
      }
   }

   public static function getArticleForm()
   {
      $xml = JDBPATH_OPTIONS . '/article.xml';
      $form = new Form("article");
      $form->load($xml);
      $formJSON = $form->get();
      return $formJSON;
   }

   public static function getFormDefaults($form)
   {
      $return = [];
      foreach ($form['tabs'] as $tab) {
         foreach ($tab['groups'] as $group) {
            foreach ($group['fields'] as $field) {
               if (isset($field['name']) && !empty($field['name'])) {
                  $return[$field['name']] = $field['value'];
               }
            }
         }
      }
      return $return;
   }

   public static function setFormCache($key, $form)
   {
      if (!static::$cache) {
         return;
      }
      $cacheFolder = JPATH_CACHE . '/' . Constants::FORMS_CACHE_DIR;
      if (!file_exists($cacheFolder)) {
         \mkdir($cacheFolder);
      }
      $cacheFile = $cacheFolder . '/' . $key . '.json';
      file_put_contents($cacheFile, \json_encode($form));
   }

   public static function getFormCache($key)
   {
      if (!static::$cache) {
         return null;
      }
      $cacheFile = JPATH_CACHE . '/' . Constants::FORMS_CACHE_DIR . '/' . $key . '.json';
      if (file_exists($cacheFile)) {
         $content = file_get_contents($cacheFile);
         return \json_decode($content);
      }
      return null;
   }

   public static function getMedia()
   {
      $folder = self::request()->get('path', '', 'RAW');
      $media = new Media();
      return $media->get($folder);
   }

   public static function getLayout()
   {
      $request = self::request();
      $id = $request->get('id', 0, 'INT');
      $type = $request->get('type', 'page');
      $lid = 0;
      $layout = '{"sections":[]}';
      if (!empty($id)) {
         $type = $request->get('type', '');
         $db = \JFactory::getDbo();
         if ($type == "page") {
            $query = "SELECT `#__jdbuilder_layouts`.* FROM `#__jdbuilder_pages` JOIN `#__jdbuilder_layouts` ON `#__jdbuilder_pages`.`layout_id`=`#__jdbuilder_layouts`.`id` WHERE `#__jdbuilder_pages`.`id`='{$id}'";
         }
         if ($type == "article" || $type == "module") {
            $query = "SELECT `#__jdbuilder_layouts`.* FROM `#__jdbuilder_layouts` WHERE `#__jdbuilder_layouts`.`id`='{$id}'";
         }
         $db->setQuery($query);
         $result = $db->loadObject();
         if (!empty($result)) {
            $lid = $result->id;
            $layout = $result->layout;
         }
      }

      $layout = \json_decode($layout, false);

      return ["id" => $lid, "layout" => $layout];
   }

   public static function savePage()
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
      }

      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      $user = \JFactory::getUser();
      // Access checks.
      if (!$user->authorise('core.create', 'com_jdbuilder')) {
         throw new \Exception(\JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
      }


      $dispatcher = \JEventDispatcher::getInstance();
      $context = 'com_jdbuilder.page';
      // Include the plugins for the save events.
      \JPluginHelper::importPlugin("content");

      $request = self::request();
      $jform = $request->get('jform', [], 'ARRAY');
      \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jdbuilder/tables');
      $table = \JTable::getInstance("Page", "JdbuilderTable", []);

      if ($table->load($jform['id'], true)) {
         if (!$table->check()) {
            throw new \Exception($table->getError());
         }
         // Trigger the before save event.
         $result = $dispatcher->trigger("onContentBeforeSave", array($context, &$table, true));

         $table->title = $jform['title'];
         $table->checked_out = $jform['checked_out'];
         $table->checked_out_time = $jform['checked_out_time'];
         $table->params = $jform['params'];
         $table->layout_id = $jform['layout_id'];
         $table->ordering = $jform['ordering'];
         $table->modified_by = $jform['modified_by'];
         $table->rules = $jform['rules'];

         $params = \json_decode($jform['params'], true);

         if (!empty($params) && is_array($params)) {
            $table->category_id = $params['category_id'];
            $table->language = $params['language'];
            $table->state = $params['state'];
            $table->access = $params['access'];
         }

         $jdbform = $request->get('_jdbform', [], 'ARRAY');
         $layout = @$jdbform['layout'];
         /* $layout = '';
         foreach ($layoutChunks as $layoutChunk) {
            $layout .= $layoutChunk;
         } */
         $db = \JFactory::getDbo();
         $object = new \stdClass();
         if (!empty($layout)) {
            if (empty($jform['layout_id'])) {
               $object->id = NULL;
               $object->layout = $layout;
               $object->created = time();
               $object->updated = time();
               $db->insertObject('#__jdbuilder_layouts', $object);
               $layoutid = $db->insertid();
               $jform['layout_id'] = $layoutid;
            } else {
               $object->id = $jform['layout_id'];
               $object->layout = $layout;
               $object->updated = time();
               $db->updateObject('#__jdbuilder_layouts', $object, 'id');
            }
         }

         if (in_array(false, $result, true) || !$table->store()) {
            throw new \Exception($table->getError());
         }

         // Trigger the after save event.
         $dispatcher->trigger("onContentAfterSave", array($context, &$table, true));
      } else {
         throw new \Exception($table->getError());
      }
      return true;
   }

   public static function getXMLFilesByElementType($type)
   {
      $type = strtolower($type);
      //$type = ($type == 'inner-row') ? 'row' : $type;
      $form_dir = JDBPATH_OPTIONS . '/';
      $plugin_element_dir = JDBPATH_ELEMENTS . '/' . $type;

      $lang = \JFactory::getLanguage();
      $lang->load();
      if (in_array($type, ['section', 'row', 'column'])) {
         $lang->load("jdbuilder", JDBPATH_PLUGIN);
         $return = [];
         $return[] = $form_dir . 'default.xml';
         $return[] = $form_dir . $type . '.xml';
      } else {
         $return = [];
         $return[] = $form_dir . 'default.xml';
         if ($type != 'inner-row') {
            $return[] = $form_dir . 'element.xml';
         }
         if (file_exists($plugin_element_dir . '/' . $type . '.xml')) {
            Helper::loadLanguage($type, $plugin_element_dir);
            $return[] = $plugin_element_dir . '/' . $type . '.xml';
         } else {
            throw new \Exception("Invalid Element");
         }
      }
      $count = 0;
      foreach ($return as $index => $file) {
         if (file_exists(str_replace('.xml', '-pro.xml', $file))) {
            array_splice($return, $index + $count + 1, 0, [str_replace('.xml', '-pro.xml', $file)]);
            $count++;
         }
      }
      return $return;
   }

   public static function renderModule($type, $value, $style)
   {
      if ($type == "module") {
         return self::renderModuleByID($value, $style);
      } else {
         return self::renderModulePosition($value, $style);
      }
   }

   public static function renderModuleByID($id, $style)
   {
      $return = [];
      $modules = \JModuleHelper::getModuleList();
      foreach ($modules as $module) {
         if ($module->id == $id) {
            $params = \json_decode($module->params, true);
            $params['style'] = $style;
            $module->params = \json_encode($params);
            $return[] = \JModuleHelper::renderModule($module);
         }
      }
      return \implode('', $return);
   }

   public static function renderModulePosition($position, $style)
   {
      $return = [];
      $modules = \JModuleHelper::getModules($position);
      foreach ($modules as $module) {
         $params = \json_decode($module->params, true);
         $params['style'] = $style;
         $module->params = \json_encode($params);
         $return[] = \JModuleHelper::renderModule($module);
      }
      return \implode('', $return);
   }

   public static function renderPage($item, $type = "page", $output = true)
   {
      $document = \JFactory::getDocument();
      $buiderConfig = \JComponentHelper::getParams('com_jdbuilder');
      if (JDB_LIVE_PREVIEW) {
         $document->addCustomTag('<link rel="stylesheet" id="jdb-preview-css" />');
         $date = new \DateTime(date('Y-m-d'), new \DateTimeZone(\JFactory::getConfig()->get('offset')));
         $document->addScriptDeclaration('var JDB_FORM_TOKEN = "' . JDB_FORM_TOKEN . '"; var JDB_RECAPTCHA_SITE_KEY = "' . $buiderConfig->get('recaptchaSiteKey', '') . '"; var JDBRenderer = null; var _JDBDATA = new Map(); var jdPageBaseUrl = "' . \JURI::root() . '"; var _JDBTIMEZONE="' . $date->format('O') . '";');
         // add shapedividers
         $fbAppId = 'var _JDBFBAPPID = "' . $buiderConfig->get('fbAppId',  '') . '";';
         $document->addScriptDeclaration($fbAppId);

         // Shape Dividers
         $dividersSVGs = 'var _JDBDIVIDERS = new Map([';
         $dividersSVGArr = [];
         $dividers = Field::getShapeDividers();
         foreach ($dividers as $divider) {
            $file = JPATH_SITE . '/media/jdbuilder/data/shape-dividers/' . $divider['value'] . '.svg';
            if (file_exists($file)) {
               $svg = file_get_contents($file);
               $dividersSVGArr[] = "['" . $divider['value'] . "','" . preg_replace("/\r|\n/", "", $svg) . "']";
            }
         }
         $dividersSVGs .= implode(',', $dividersSVGArr) . ']);';
         $document->addScriptDeclaration($dividersSVGs);

         // Particles background presets
         $particlesPresets = 'var _JDBPARTICLESPRESETS = {';
         $presetsArr = [];
         $presets = Field::getParticlesPresets();
         foreach ($presets as $preset) {
            $file = JPATH_SITE . '/media/jdbuilder/data/particles-presets/' . $preset['value'] . '.json';
            if (file_exists($file)) {
               $json = file_get_contents($file);
               $presetsArr[] = $preset['value'] . ": '" . \json_encode(\json_decode($json)) . "'";
            }
         }
         $particlesPresets .= implode(',', $presetsArr) . '};';
         $document->addScriptDeclaration($particlesPresets);

         $document->addScriptDeclaration('var _JDB_ITEM_TYPE = "page";');
         $document->addScriptDeclaration('var _JDBGLOBALSETTINGS = ' . Helper::globalSettings()->toString() . ';');
         $document->addScriptDeclaration('var _JDB_ITEM_ID = ' . $item->id . ';');

         if (file_exists(JDBPATH_PLUGIN . '/fonts/fonts.json')) {
            $customFonts = \json_decode(file_get_contents(JDBPATH_PLUGIN . '/fonts/fonts.json'), true);
         } else {
            $customFonts = [];
         }
         $customFontsScript = 'var _JDBCFONTS = new Map([';
         $customFontsArr = [];
         if (!empty($customFonts)) {
            foreach ($customFonts as $id => $customFont) {
               $arr = ['files' => $customFont['files'], 'name' => $customFont['name']];
               $customFontsArr[] = '["' . $id . '", ' . \json_encode($arr) . ']';
            }
         }
         $customFontsScript .= (implode(',', $customFontsArr)) . ']);';
         $document->addScriptDeclaration($customFontsScript);


         $document->addScript(\JURI::root() . 'media/jdbuilder/js/jdb.min.js', ['version' => JDB_MEDIA_VERSION]);

         // async defer
         if (!empty($buiderConfig->get('gmapkey', ''))) {
            $document->addCustomTag('<script src="https://maps.googleapis.com/maps/api/js?key=' . $buiderConfig->get('gmapkey', '') . '" async defer></script>');
         }

         /*
         $document->addScriptDeclaration('
               var afterPreviewCompileTimeout = null;
               function beforePreviewCompile(){
                  clearParticles();
                  clearTimeout(afterPreviewCompileTimeout);
               }
               function afterPreviewCompile(){
                  afterPreviewCompileTimeout = setTimeout(function(){
                     makeParticles();
                  }, 400);
               }
         ');
         */
         self::getAdminElements();

         Helper::renderGlobalScss();

         $document->addStylesheet(JDBURL_MEDIA . '/css/preview.css');
         $document->addStylesheet('https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap');
         Builder::getDocument()->materialIcons = true;
         Builder::getDocument()->faIcons = true;
         Builder::getDocument()->foundationIcons = true;
         Builder::getDocument()->typeIcons = true;
         Builder::getDocument()->animateCss = true;
         $document->addCustomTag('<script src="https://www.google.com/recaptcha/api.js" async defer></script>');

         Helper::renderGlobalTypography();
         $css = self::renderStyle();
         $document->addStyleDeclaration($css);

         return self::livePreviewArea();
      }

      $layout = Helper::getLayout($item->layout_id);
      $page = Helper::getPage($item->id);


      $params = new \JRegistry();
      if (isset($page->params)) {
         $params->loadObject(\json_decode($page->params));
      }

      $title = $params->get('metaTitle', '');
      $description = $params->get('metaDescription', '');
      $keywords = $params->get('metaKeywords', '');

      $ogTitle = $params->get('ogTitle', '');
      $ogDescription = $params->get('ogDescription', '');
      $ogImage = $params->get('ogImage', '');

      if (!empty($title)) {
         $document->setTitle($title);
      }

      if (!empty($description)) {
         $document->addCustomTag('<meta name="description" content="' . $description . '">');
      }

      if (!empty($keywords)) {
         $document->addCustomTag('<meta name="keywords" content="' . $keywords . '">');
      }

      if (!empty($ogTitle)) {
         $document->addCustomTag('<meta property="og:title" content="' . $ogTitle . '" />');
      }

      if (!empty($ogDescription)) {
         $document->addCustomTag('<meta property="og:description" content="' . $ogDescription . '" />');
      }

      if (!empty($ogImage)) {
         $document->addCustomTag('<meta property="og:image" content="' . Helper::mediaValue($ogImage) . '" />');
      }
      if (!empty($ogTitle) || !empty($ogDescription) || !empty($ogImage)) {
         $document->addCustomTag('<meta name="twitter:card" content="summary" />');
      }

      Helper::renderGlobalScss();

      if (empty($layout)) {
         return '';
      }

      $layout = new Element\Layout($layout, 'page', $item->id);
      $rendered = $layout->render();
      \JDPageBuilder\Builder::renderPageStyle($params);
      \JDPageBuilder\Helper::renderGlobalTypography();
      \JDPageBuilder\Builder::renderHead();
      echo $rendered;
   }

   public static function renderPageStyle($params)
   {
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

         $color = $params->get('page' . $type . 'Color', '');
         if ($color !== '') {
            $typeStyle->addCss('color', $color);
         }

         $linkColor = $params->get('page' . $type . 'LinkColor', '');
         if ($linkColor !== '') {
            $linkStyle->addCss('color', $linkColor);
         }

         $linkHoverColor = $params->get('page' . $type . 'LinkHoverColor', '');
         if ($linkHoverColor !== '') {
            $linkHoverStyle->addCss('color', $linkHoverColor);
         }

         $typography = $params->get('page' . $type . 'Typography', null);
         if (!empty($typography)) {
            foreach (Helper::$devices as $deviceKey => $device) {
               if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
                  $typeStyle->addStyle(Helper::typographyValue($typography->{$deviceKey}), $device);
               }
            }
         }

         $typeStyle->render();
         $linkStyle->render();
         $linkHoverStyle->render();
      }

      $customCss = $params->get('custom_css', null);
      if (!empty($customCss)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($customCss->{$deviceKey}) && !empty($customCss->{$deviceKey})) {
               \JDPageBuilder\Helper::customCSS($customCss->{$deviceKey}, null, $device);
            }
         }
      }

      $customJs = $params->get('javascript', '');
      if (!empty($customJs)) {
         Builder::addScript($customJs);
      }
   }

   public static function livePreviewArea()
   {
      $return = '<div id="jdb-livepreview"></div>';
      echo $return;
   }

   public static function onBeforeBodyClose()
   {
      if (JDB_LIVE_PREVIEW) {
         $version = Helper::getMediaVersion();
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/jquery-3.4.1.min.js?v=' . $version . '"></script>';  
         echo '<script>var $JDB = jQuery.noConflict();</script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/preview.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/particles.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/animatedheading.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/isotope.pkgd.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/parsley.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/imask.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/jdvideo.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/jdytsubscriber.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/selectr.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/parsley.custom.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/jquery.justifiedGallery.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/jdlightbox.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/jquery.event.move.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/moment.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/pikaday.min.js?v=' . $version . '"></script>';
         echo '<script src="' . \JURI::root() . 'media/jdbuilder/js/jdbfrontend.js?v=' . $version . '"></script>';
         echo '<script>jQuery.noConflict(true);</script>';
      }

      if (!empty(self::$scripts['body'])) {
         echo '<script>' . implode("\n", self::$scripts['body']) . '</script>';
      }
   }

   public static function renderHead()
   {
      // Add Rendered CSS in Head
      $document = \JFactory::getDocument();

      $document->addScript(\JURI::root() . 'media/jdbuilder/js/jquery-3.4.1.min.js', ['version' => JDB_MEDIA_VERSION]);
      $document->addScriptDeclaration('var $JDB = jQuery.noConflict();');

      // Add Rendered JS Files in Head
      foreach (self::$javascripts as $javascript) {
         $document->addScript($javascript, ['version' => 'auto']);
      }

      // Add Rendered Javascript in Head
      foreach (self::$scripts['head'] as $script) {
         $document->addScriptDeclaration($script);
      }

      $document->addScriptDeclaration('jQuery.noConflict(true);');
      Helper::renderGlobalScss();



      $document->addScript(\JURI::root() . 'media/jdbuilder/js/jdb.min.js', ['version' => JDB_MEDIA_VERSION]);

      $css = self::renderStyle();
      $document->addStyleDeclaration($css);
   }

   public static function renderElement($object)
   {
      $element = new Element\Element($object);
      return $element->renderElement();
   }

   public static function loadFontLibraryByIcon($icon = '')
   {
      if (empty($icon)) {
         return;
      }
      $prefix = substr($icon, 0, 2);
      if (!in_array($prefix, ['fa', 'fi', 'ty'])) {
         return;
      }
      switch ($prefix) {
         case 'fa':
            Builder::getDocument()->faIcons = true;
            break;
         case 'fi':
            Builder::getDocument()->foundationIcons = true;
            break;
         case 'ty':
            Builder::getDocument()->typeIcons = true;
            break;
      }
   }

   public static function loadGoogleMap($callback)
   {
      Builder::getDocument()->googleMap = true;
      if (!in_array($callback, Builder::getDocument()->googleMapCallbacks)) {
         Builder::getDocument()->googleMapCallbacks[] = $callback;
      }
   }

   public static function loadAnimateCSS()
   {
      Builder::getDocument()->animateCss = true;
   }

   public static function loadLightBox()
   {
      Builder::getDocument()->lightBox = true;
   }

   public static function loadParticleJS($id, $params)
   {
      self::addJavascript(\JURI::root() . 'media/jdbuilder/js/particles.min.js');
      $script = 'particlesJS(\'' . $id . '\', ' . \json_encode($params) . ')';
      self::addScript($script);
   }

   public static function loadAnimatedHeadingJS()
   {
      self::addJavascript(\JURI::root() . 'media/jdbuilder/js/animatedheading.js');
   }

   public static function getFonts()
   {
      $return = [];
      $return['options'] = [['label' => \JText::_('JDB_DEFAULT'), 'value' => '']];

      $system_fonts = [];
      $system_fonts['label'] = \JText::_('JDB_SYSTEM_FONTS_TITLE');
      $system_fonts['type'] = "system";
      $system_fonts['options'] = Field::getSystemFonts();
      $return['groups'][] = $system_fonts;


      $google_fonts = [];
      $google_fonts['label'] = \JText::_('JDB_GOOGLE_FONTS_TITLE');
      $google_fonts['type'] = "google";
      $google_fonts['options'] = \json_decode(file_get_contents(JPATH_SITE . '/media/jdbuilder/data/googlefonts.json'));
      $return['groups'][] = $google_fonts;

      return $return;
   }

   public static function newFolder()
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JDB_ERROR_NOT_PERMITTED'));
      }
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      return Media::create();
   }

   public static function deleteMedia()
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JDB_ERROR_NOT_PERMITTED'));
      }
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      return Media::delete();
   }

   public static function copyMedia()
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JDB_ERROR_NOT_PERMITTED'));
      }
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      return Media::copy();
   }

   public static function renameMedia()
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JDB_ERROR_NOT_PERMITTED'));
      }
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      return Media::rename();
   }

   public static function uploadMedia()
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JDB_ERROR_NOT_PERMITTED'));
      }
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      return Media::upload();
   }

   public static function addFavourite()
   {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      $request = self::request();
      $id = $request->get('id', 0, 'INT');
      if (empty($id)) {
         throw new \Exception("Invalid Template ID");
      }
      $db = \JFactory::getDbo();

      $object = new \stdClass();
      $object->template_id = $id;
      $object->created = time();
      $object->updated = time();

      $db->insertObject('#__jdbuilder_favourites', $object);
      return true;
   }

   public static function removeFavourite()
   {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      $request = self::request();
      $id = $request->get('id', 0, 'INT');
      if (empty($id)) {
         throw new \Exception("Invalid Template ID");
      }
      $db = \JFactory::getDbo();
      $query = "DELETE FROM `#__jdbuilder_favourites` WHERE `template_id`='{$id}'";
      $db->setQuery($query);
      $db->execute();
      return true;
   }

   public static function download()
   {
      $request = self::request();
      $content = $request->get('json', [], 'ARRAY');
      return $content;
   }

   public static function cleanGlobalCache()
   {
      Helper::clearGlobalCSS();
      return true;
   }

   public static function saveTemplate()
   {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      $request = self::request();
      $data = $request->get('data', '{}', 'RAW');
      $title = $request->get('title', '', 'RAW');
      if (empty($title)) {
         throw new \Exception("Invalid title");
      }

      $data = \json_decode($data, false);

      switch ($data->type) {
         case 'layout':
         case 'section':
            $type = $data->type;
            break;
         default:
            $type = 'element';
            break;
      }

      $template = new \stdClass();
      $template->id = null;
      $template->title = $title;
      $template->type = $type;
      $template->template = \json_encode($data);
      $template->created = time();
      $template->updated = time();

      $db = \JFactory::getDbo();
      $db->insertObject('#__jdbuilder_templates', $template);
      return true;
   }

   public static function getSavedTemplates()
   {
      $db = \JFactory::getDbo();
      $query = "SELECT `id`,`title`,`type`,`created` FROM `#__jdbuilder_templates` WHERE `type`!='element'";
      $db->setQuery($query);
      $results = $db->loadObjectList();
      foreach ($results as &$result) {
         $result->created = date('M d, Y', $result->created);
         $result->type = ($result->type == 'layout' ? 'Page' : 'Section');
      }
      return $results;
   }

   public static function getTemplate()
   {
      $request = self::request();
      $db = \JFactory::getDbo();

      $id = $request->get('id', 0, 'INT');
      $query = "SELECT `template`,`type` FROM `#__jdbuilder_templates` WHERE `id`='{$id}'";
      $db->setQuery($query);
      $result = $db->loadObject();
      if (empty($result)) {
         throw new \Exception("Template not found.");
      }
      $template = \json_decode($result->template, false);
      $type = $result->type == 'section' ? 'block' : $result->type;
      return ['type' => $type, 'template' => $template];
   }

   public static function deleteTemplate()
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JDB_ERROR_NOT_PERMITTED'));
      }
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDB_ERROR_INVALID_SESSION'));
      }
      $request = self::request();
      $db = \JFactory::getDbo();

      $id = $request->get('id', 0, 'INT');
      $query = "DELETE FROM `#__jdbuilder_templates` WHERE `id`='{$id}'";
      $db->setQuery($query);
      $db->execute();

      return true;
   }

   public static function getAdminElements()
   {
      $document = \JFactory::getDocument();
      $path = JDBPATH_ELEMENTS;
      $dirs = array_filter(glob($path . '/*'), 'is_dir');
      $files = [];
      $templates = '<!-- JD Builder Element\'s Templates -->';
      foreach ($dirs as $dir) {

         if (in_array(strtolower(basename($dir)), self::$reserved_elements)) {
            continue;
         }

         $javascript = file_exists($dir . '/' . 'tmpl/default.js') ? basename($dir) . '/' . 'tmpl/default.js' : basename($dir) . '/' . 'tmpl/' . basename($dir) . '.js';


         if (file_exists($path . '/' . $javascript)) {
            $files[] = $path . '/' . $javascript;
         }

         $helper = basename($dir) . '/' . 'helper.js';
         if (file_exists($path . '/' . $helper)) {
            $files[] = $path . '/' . $helper;
         }

         $templates .= Helper::getElementPartials($dir);
      }

      $templates .= '<!-- End Templates -->';


      $script = Helper::minifyJS($files);
      $document->addScript(\JURI::root() . 'media/jdbuilder/js/mustache.min.js');
      $document->addScriptDeclaration($script);
      $document->addCustomTag($templates);
      //JDPageBuilder\Helper::minifyJS
   }

   public static function downloadExternalMedia()
   {
      return Media::download();
   }

   public static function getCategories()
   {
      $return = [];
      $extensions = ['com_content'];
      foreach ($extensions as $extension) {
         $object = ['extension' => $extension, 'categories' => []];
         $options = \JHtml::_('category.options', $extension);
         $categories = [];
         foreach ($options as $option) {
            $categories[] = ['id' => $option->value, 'title' => $option->text];
         }
         $object['categories'] = $categories;
         $return[] = $object;
      }
      return $return;
   }

   public static function getArticles()
   {
      $db = \JFactory::getDbo();
      $query = "SELECT `id`,`title` FROM `#__content` WHERE `state`='1'";
      $db->setQuery($query);
      return $db->loadObjectList();
   }

   public static function getMenuitems()
   {
      require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
      $return = [];
      $groups = [];
      $items = \MenusHelper::getMenuLinks('', 0, 0, [], [], 0);
      // Build the groups arrays.
      foreach ($items as $menu) {
         // Initialize the group.
         $groups[$menu->title] = [];

         // Build the options array.
         foreach ($menu->links as $link) {
            $levelPrefix = str_repeat('- ', max(0, $link->level - 1));

            // Displays language code if not set to All
            if ($link->language !== '*') {
               $lang = ' (' . $link->language . ')';
            } else {
               $lang = '';
            }

            $groups[$menu->title][] = \JHtml::_(
               'select.option',
               $link->value,
               $levelPrefix . $link->text . $lang,
               'value',
               'text',
               false
            );
         }
      }

      $return = ['options' => [['label' => 'None', 'value' => '']], 'groups' => []];
      foreach ($groups as $groupTitle => $group) {
         $options = [];
         foreach ($group as $option) {
            $options[] = ['label' => $option->text, 'value' => $option->value];
         }
         $return['groups'][] = ['label' => $groupTitle, 'options' => $options];
      }

      return $return;
   }

   public static function jdApi()
   {
      $request = self::request();
      return Helper::jdApiRequest($request->get('method', 'post'), $request->get('hook', '', 'RAW'), $request->get('data', [], 'ARRAY'));
   }

   public static function getData()
   {
      $request = self::request();
      $task = $request->get('data', '');
      $task = \JDPageBuilder\Helper::classify($task);
      if (!method_exists(\JDPageBuilder\Helpers\DataHelper::class, $task)) {
         throw new \Exception('Bad Data Request', 400);
      } else {
         $methodName = $task;
      }
      return \JDPageBuilder\Helpers\DataHelper::$methodName();
   }

   public static function getAjax()
   {
      $request = self::request();
      $element = $request->get('element', '');
      $q = $request->get('q', '', 'RAW');
      $_type = $request->get('_type', 'query');
      $key = $request->get('key', '');
      if (empty($element) || empty($key)) {
         throw new \Exception('Bad Ajax Request', 400);
      }
      if ($_type === 'init') {
         $result = self::getAjaxOptions($element, $key, $q, true);
      } else {
         $result = self::getAjaxOptions($element, $key, $q);
      }
      $result = empty($result) ? [] : $result;
      return $result;
   }

   public static function getAjaxOptions($element, $key, $value, $init = false)
   {
      if (!file_exists(JDBPATH_ELEMENTS . '/' . $element . '/helper.php')) {
         throw new \Exception('Bad Ajax Request', 400);
      }

      require_once(JDBPATH_ELEMENTS . '/' . $element . '/helper.php');

      $class = 'JDBuilder' . \JDPageBuilder\Helper::classify($element) . 'ElementHelper';

      $func = \JDPageBuilder\Helper::classify(($init ? 'init' : 'query') . 'Ajax_' . $key);

      if (!method_exists($class, $func)) {
         throw new \Exception('Bad Ajax Request', 400);
      } else {
         $namespace = new $class();
         return $namespace->$func($value);
      }
   }
}
