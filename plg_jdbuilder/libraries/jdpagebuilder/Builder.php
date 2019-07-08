<?php

namespace JDPageBuilder;

abstract class Builder {

   protected static $request = null;
   protected static $styles = [];
   protected static $scripts = [];
   protected static $stylesheets = [];
   protected static $javascripts = [];
   protected static $authorised = null;
   protected static $forms = [];
   protected static $cache = false;
   protected static $debug = false;
   protected static $debugmarker = null;
   protected static $logs = [];
   public static $reserved_elements = ["section", "row", "column", "element", "__page"];

   public static function request() {
      if (!static::$request) {
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            static::$request = \JFactory::getApplication()->input->post;
         } else {
            static::$request = \JFactory::getApplication()->input->get;
         }
      }
      return static::$request;
   }

   public static function authorised() {
      if (static::$authorised === null) {
         static::$authorised = \JAccess::getAuthorisedViewLevels(\JFactory::getUser()->get('id'));
      }
      return static::$authorised;
   }

   public static function debugging($status = "start") {
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

   public static function log($message = '', $type = 'info') {
      static::$logs[] = ['type' => $type, 'message' => $message];
   }

   public static function getRunTime($ru, $rus, $index) {
      return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000)) - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
   }

   public static function addStyle($css = '') {
      static::$styles[] = $css;
   }

   public static function addScript($js = '') {
      static::$scripts[] = $js;
   }

   public static function addStylesheet($file) {
      static::$stylesheets[$file] = $file;
   }

   public static function addJavascript($file) {
      static::$javascripts[$file] = $file;
   }

   public static function builderButton($enabled = false) {
      $layout = new \JLayoutFile('button', JPATH_PLUGINS . '/system/jdbuilder/layouts');
      return $layout->render(['enabled' => $enabled]);
   }

   public static function builderArea($enabled = false, $type = 'page', $id = 0) {
      $layout = new \JLayoutFile('builder', JPATH_PLUGINS . '/system/jdbuilder/layouts');
      return $layout->render(['enabled' => $enabled, 'type' => $type, 'id' => $id]);
   }

   public static function JDBBanner() {
      $layout = new \JLayoutFile('banner', JPATH_PLUGINS . '/system/jdbuilder/layouts');
      return $layout->render();
   }

   public static function getElements() {
      $paths = [
          JPATH_PLUGINS . '/system/jdbuilder/elements'
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

   public static function getLivePreview() {
      $request = self::request();
      $params = $request->get('params', '{}', 'RAW');
      $lid = $request->get('lid', 0, 'INT');
      $id = $request->get('id', 0, 'INT');

      $layout = new \stdClass();
      $layout->id = $lid;
      $layout->layout = $params;
      $layout = new Element\Layout($layout);
      $document = \JFactory::getDocument();
      $return = [];
      $beforehead = $document->getHeadData();
      $html = $layout->render();
      $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
      $return['html'] = $html;
      $afterhead = $document->getHeadData();
      $return['head'] = Builder::getRenderHead($id, $beforehead, $afterhead);
      return $return;
   }

   public static function getElementsByPath($path) {
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
               $elements[] = self::getElementDataByManifest($manifest);
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

   public static function getElementDataByManifest($manifest) {
      $xml = \JFactory::getXml($manifest);
      $element = new \stdClass();
      $element->type = (string) $xml->attributes()->type;
      $element->title = (string) $xml->title;
      $element->icon = (string) $xml->icon;
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

   public static function getForm($type = null) {
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

   public static function getPageForm() {
      $xml = JPATH_PLUGINS . '/system/jdbuilder/options/page.xml';
      $form = new Form("page");
      $form->load($xml);
      $formJSON = $form->get();
      return $formJSON;
   }

   public static function getFormDefaults($form) {
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

   public static function setFormCache($key, $form) {
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

   public static function getFormCache($key) {
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

   public static function getMedia() {
      $folder = self::request()->get('path', '', 'RAW');
      $media = new Media();
      return $media->get($folder);
   }

   public static function getLayout() {
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

   public static function savePage() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
      }
      $user = \JFactory::getUser();
      // Access checks.
      if (!$user->authorise('core.create', 'com_jdbuilder')) {
         throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
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
         }

         $jdbform = $request->get('_jdbform', [], 'ARRAY');
         $layout = @$jdbform['layout'];
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

   public static function getXMLFilesByElementType($type) {
      $type = strtolower($type);
      //$type = ($type == 'inner-row') ? 'row' : $type;
      $form_dir = JPATH_PLUGINS . '/system/jdbuilder/options/';
      $plugin_element_dir = JPATH_PLUGINS . '/system/jdbuilder/elements/' . $type;

      $lang = \JFactory::getLanguage();
      $lang->load();
      if (in_array($type, ['section', 'row', 'column'])) {
         $lang->load("jdbuilder", JPATH_PLUGINS . '/system/jdbuilder');
         $return = [];
         $return[] = $form_dir . 'default.xml';
         $return[] = $form_dir . $type . '.xml';
         return $return;
      } else {
         $return = [];
         $return[] = $form_dir . 'default.xml';
         $return[] = $form_dir . 'element.xml';
         if (file_exists($plugin_element_dir . '/' . $type . '.xml')) {
            Helper::loadLanguage($type, $plugin_element_dir);
            $return[] = $plugin_element_dir . '/' . $type . '.xml';
         } else {
            throw new \Exception("Invalid Element");
         }
         return $return;
      }
   }

   public static function renderModule($type, $value, $style) {
      if ($type == "module") {
         return self::renderModuleByID($value, $style);
      } else {
         return self::renderModulePosition($value, $style);
      }
   }

   public static function renderModuleByID($id, $style) {
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

   public static function renderModulePosition($position, $style) {
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

   public static function renderPage($item, $type = "page", $output = true) {
      $layout = Helper::getLayout($item->layout_id);
      $page = Helper::getPage($item->id);


      $params = new \JRegistry();
      if (isset($page->params)) {
         $params->loadObject(\json_decode($page->params));
      }

      $document = \JFactory::getDocument();
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

      if (empty($layout)) {
         return false;
      }

      $layout = new Element\Layout($layout);
      $return = $layout->render();

      if ($output) {
         echo $return;
      } else {
         return $return;
      }
   }

   public static function renderHead($id = null) {
      // Add Rendered CSS in Head
      $document = \JFactory::getDocument();
      $css = [];
      foreach (self::$styles as $style) {
         $css[] = $style;
      }

      $scss = implode('', $css);
      $css = Helper::compileSass($scss, $id);

      $css = '<style id="jdb-styles">' . $css . '</style>';

      $document->addCustomTag($css);

      // Add CSS Files in Head
      foreach (self::$stylesheets as $stylesheet) {
         $document->addStyleSheet($stylesheet);
      }

      // Add Rendered JS Files in Head
      foreach (self::$javascripts as $javascript) {
         $document->addScript($javascript);
      }

      // Add Rendered Javascript in Head
      foreach (self::$scripts as $script) {
         $document->addScriptDeclaration($script);
      }

      Helper::renderGlobalScss();

      $document->addScript(\JURI::root() . 'media/jdbuilder/js/jdb.min.js', ['version' => $document->getMediaVersion()]);

      $request = \JDPageBuilder\Builder::request();
      if ($request->get('jdb-preview', 0)) {
         $document->addScript(\JURI::root() . 'media/jdbuilder/js/preview.js', ['version' => $document->getMediaVersion()]);
         $document->addStylesheet(\JURI::root() . 'media/jdbuilder/css/preview.css', ['version' => $document->getMediaVersion()]);
      }

      $document->addStylesheet('//cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.0/animate.min.css', ['version' => $document->getMediaVersion()]);
   }

   public static function getRenderHead($id = null, $before = [], $after = []) {
      // Add Rendered CSS in Head
      $css = [];
      foreach (self::$styles as $style) {
         $css[] = $style;
      }
      $scss = implode('', $css);
      $css = Helper::compileSass($scss, "jdbl-" . $id);

      $imports = [];
      foreach (self::$stylesheets as $stylesheet) {
         $imports[] = '@import "' . $stylesheet . '";';
      }

      if ($after["styleSheets"] != $before["styleSheets"]) {
         $after_styles = array_keys($after["styleSheets"]);
         $before_styles = array_keys($before["styleSheets"]);
         $styleSheets = array_diff($after_styles, $before_styles);
         if (!empty($styleSheets)) {
            foreach ($styleSheets as $styleSheet) {
               $imports[] = '@import "' . $styleSheet . '";';
            }
         }
      }

      return '<style id="jdb-styles">' . implode($imports) . $css . '</style>';
   }

   public static function renderElement($object) {
      $element = new Element\Element($object);
      return $element->renderElement();
   }

   public static function loadFontLibraryByIcon($icon = '') {
      if (empty($icon)) {
         return;
      }
      $prefix = substr($icon, 0, 2);
      if (!in_array($prefix, ['fa', 'fi', 'ty'])) {
         return;
      }

      switch ($prefix) {
         case 'fa':
            self::addStylesheet('//use.fontawesome.com/releases/v5.7.0/css/all.css');
            break;
         case 'fi':
            self::addStylesheet('//cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.min.css');
            break;
         case 'ty':
            self::addStylesheet('//cdnjs.cloudflare.com/ajax/libs/typicons/2.0.9/typicons.min.css');
            break;
      }
   }

   public static function getFonts() {
      $return = [];
      $return['options'] = [['label' => \JText::_('JDEFAULT'), 'value' => '']];


      $system_fonts = [];
      $system_fonts['label'] = \JText::_('JDBUILDER_SYSTEM_FONTS_TITLE');
      $system_fonts['type'] = "system";
      $system_fonts['options'] = Field::getSystemFonts();
      $return['groups'][] = $system_fonts;


      $google_fonts = [];
      $google_fonts['label'] = \JText::_('JDBUILDER_GOOGLE_FONTS_TITLE');
      $google_fonts['type'] = "google";
      $google_fonts['options'] = \json_decode(file_get_contents(JPATH_SITE . '/media/jdbuilder/data/googlefonts.json'));
      $return['groups'][] = $google_fonts;

      return $return;
   }

   public static function newFolder() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
      }
      return Media::create();
   }

   public static function deleteMedia() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
      }
      return Media::delete();
   }

   public static function copyMedia() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
      }
      return Media::copy();
   }

   public static function renameMedia() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
      }
      return Media::rename();
   }

   public static function uploadMedia() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
      }
      return Media::upload();
   }

   public static function addFavourite() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
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

   public static function removeFavourite() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
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

   public static function download() {
      $request = self::request();
      $content = $request->get('json', [], 'ARRAY');
      return $content;
   }

   public static function cleanGlobalCache() {
      Helper::clearGlobalCSS();
      return true;
   }

   public static function saveTemplate() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
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

   public static function getSavedTemplates() {
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

   public static function getTemplate() {
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

   public static function deleteTemplate() {
      if (!\JSession::checkToken()) {
         throw new \Exception(\JText::_('JDBUILDER_ERROR_INVALID_SESSION'));
      }
      $request = self::request();
      $db = \JFactory::getDbo();

      $id = $request->get('id', 0, 'INT');
      $query = "DELETE FROM `#__jdbuilder_templates` WHERE `id`='{$id}'";
      $db->setQuery($query);
      $db->execute();

      return true;
   }

}
