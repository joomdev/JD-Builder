<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

JLoader::registerNamespace('JDPageBuilder', JPATH_PLUGINS . '/system/jdbuilder/libraries/jdpagebuilder', false, false, 'psr4');
JLoader::registerNamespace('JDPageBuilder\\Element', JPATH_PLUGINS . '/system/jdbuilder/libraries/jdpagebuilder/element', false, false, 'psr4');

class plgSystemJDBuilder extends JPlugin
{

   protected $app;
   static $article_layouts = [];

   function __construct(&$subject, $config)
   {
      parent::__construct($subject, $config);
      if ($this->app->isAdmin()) {
         define('JDB_DEBUG', $this->params->get('debug', 0));
         define('JDB_KEY', $this->params->get('key', '', 'RAW'));

         $xml = JFactory::getXML(JPATH_PLUGINS . '/system/jdbuilder/jdbuilder.xml');
         $version = (string) $xml->version;
         define('JDB_VERSION', $version);


         \JDPageBuilder\Helper::loadLanguage();
      }
   }

   // Events 

   public function onBeforeRender()
   {

      $request = \JDPageBuilder\Builder::request();

      if ($this->isPageView() && !$this->app->isAdmin()) {
         $id = $this->app->input->get('id', null);
         if (!empty($id)) {
            \JDPageBuilder\Builder::renderHead("jdbl-" . $id);
         }
      }

      if ($request->get('jdb-api', 0, "INT")) {

         if ($request->get('download', 0, "INT")) {
            $filename = $request->get('filename', time(), 'RAW');
            header('Content-disposition: attachment; filename=' . $filename . '.json');
         }
         header('Content-Type: application/json');
         $return = [];
         try {
            \JDPageBuilder\Builder::debugging();
            $task = $request->get('task', '');
            $task = \JDPageBuilder\Helper::classify($task);
            if (!method_exists(\JDPageBuilder\Builder::class, $task)) {
               if (method_exists(\JDPageBuilder\Builder::class, 'get' . ucfirst($task))) {
                  $methodName = 'get' . ucfirst($task);
               } else {
                  throw new \Exception('Bad Request', 400);
               }
            } else {
               $methodName = $task;
            }
            $data = NULL;
            $data = \JDPageBuilder\Builder::$methodName();
            $return['status'] = 'success';
            $return['code'] = 200;
            $return['data'] = $data;
         } catch (\Exception $e) {
            $return['status'] = 'error';
            $return['message'] = $e->getMessage();
            $return['code'] = $e->getCode();
         }

         $debuginfo = \JDPageBuilder\Builder::debugging("stop");
         if (!empty($debuginfo)) {
            $return['debug'] = $debuginfo;
         }

         echo \json_encode($return);
         exit;
      }
   }

   public function onAfterRender()
   {
      if ($this->app->isAdmin()) {
         $this->addDescription();
      }
      if (!$this->app->isAdmin() || !$this->isValidView()) {
         return;
      }
      if ($this->isPageEdit()) {
         $id = $this->app->input->get('id', 0, 'INT');
         return $this->addBuilder($id);
      }
      if ($this->isArticleEdit()) {
         $id = $this->app->input->get('id', 0, 'INT');
         return $this->addBuilderOnArticle($id);
      }
   }

   public function onBeforeCompileHead()
   {
      $docuemnt = \JFactory::getDocument();
      //$docuemnt->addScript(JURI::root() . "media/jdbuilder/js/default-passive-events.js", ["version" => $docuemnt->getMediaVersion()]);
      $docuemnt->addScriptDeclaration("var _JDB = {};");
      $docuemnt->addScript(JURI::root() . 'media/system/js/core.js');
      if (!$this->app->isAdmin() || !$this->isValidView()) {
         return;
      }

      if ($this->isPageEdit() || $this->isArticleEdit()) {
         \JDPageBuilder\Helper::loadBuilderLanguage();
         \JDPageBuilder\Builder::getAdminElements();
         $id = $this->app->input->get('id', 0);
         $style = '#jdbuilder-area{display: none;}#jdbuilder-area.active{display: block;}#jdbuilder-area.active~div{display: none;}#jdbuilder-area{position: relative;}#jdbuilder-area.loading{height: 400px; overflow: hidden}#jdbuilder-controls .btn-jdb-exit{display: none;}#jdbuilder-controls .btn-jdb-fs{display: none;}#jdbuilder-controls.active .btn-jdb-exit{display: inline-block;}#jdbuilder-controls.active .btn-jdb-edit{display: none;}#jdbuilder-controls.active .btn-jdb-fs{display: inline-block;}';

         $docuemnt->addStyleDeclaration($style);
         $docuemnt->addStyleSheet('//fonts.googleapis.com/css?family=Noto+Sans:400,700');
         $docuemnt->addStyleSheet(JURI::root(true) . '/media/jdbuilder/css/style.min.css', ['version' => $docuemnt->getMediaVersion()]);
         $docuemnt->addStyleSheet(JURI::root(true) . '/media/jdbuilder/css/rtl.css', ['version' => $docuemnt->getMediaVersion()]);
         $docuemnt->addStyleSheet(JURI::root(true) . '/media/jdbuilder/js/builder/styles.css', ['version' => $docuemnt->getMediaVersion()]);
      }
   }

   // Functions

   public function isValidView()
   {
      $option = $this->app->input->get('option', '');
      return ($option == "com_jdbuilder" || $option == "com_content");
   }

   public function isPageView()
   {
      $option = $this->app->input->get('option', '');
      $view = $this->app->input->get('view', '');
      $id = $this->app->input->get('id', 0, 'INT');
      return ($option == "com_jdbuilder" && $view == "page" && !empty($id));
   }

   public function isArticleListing()
   {
      $option = $this->app->input->get('option', '');
      $view = $this->app->input->get('view', '');
      $layout = $this->app->input->get('layout', '');
      return ($option == "com_content" && ($view == "articles" || $view == "") && $layout == "");
   }

   public function isArticleEditing()
   {
      $option = $this->app->input->get('option', '');
      $task = $this->app->input->get('task', '');
      return ($option == "com_content" && $task == "article.edit");
   }

   public function isArticleEdit()
   {
      $option = $this->app->input->get('option', '');
      $view = $this->app->input->get('view', '');
      $layout = $this->app->input->get('layout', '');
      return ($option == "com_content" && $view == "article" && $layout == "edit");
   }

   public function isPageEdit()
   {
      $option = $this->app->input->get('option', '');
      $view = $this->app->input->get('view', '');
      $layout = $this->app->input->get('layout', '');
      return ($option == "com_jdbuilder" && $view == "page" && $layout == "edit");
   }

   public function addBodyClass()
   {
      $body = $this->app->getBody();
      $body = preg_replace_callback('/(<body\s[^>]*class=")([^"]*)("[^>]*>)(.*)(<\/body>)/siU', function ($matches) {
         $class = $matches[2];
         $class = empty($class) ? 'jdbuilder' : $class . ' jdbuilder';
         $html = str_replace('class="' . $matches[2] . '"', 'class="' . $class . '"', $matches[0]);
         return $html;
      }, $body);
      $this->app->setBody($body);
   }

   public function addBuilder($id)
   {
      $this->addBodyClass();
      $body = $this->app->getBody();
      $body = str_replace('{jdbuilder}', \JDPageBuilder\Builder::builderArea(true, 'page', $id), $body);
      $this->app->setBody($body);
   }

   public function addBuilderOnArticle($id, $enabled = true)
   {
      $this->addBodyClass();
      $body = $this->app->getBody();
      $body = str_replace('<fieldset class="adminform">', \JDPageBuilder\Builder::builderArticleToggle($enabled, $id) . '<fieldset class="adminform">' . \JDPageBuilder\Builder::builderArea($enabled, 'article', $id), $body);
      $this->app->setBody($body);
   }

   public function addDescription()
   {
      $body = $this->app->getBody();
      $option = $this->app->input->get('option', '');
      $view = $this->app->input->get('view', '');
      if ($option == "com_installer" && $view == "install") {
         $body = str_replace('{jdbcomdesc}', \JDPageBuilder\Builder::JDBBanner(), $body);
         $body = str_replace('{jdbplgdesc}', \JDPageBuilder\Builder::JDBBanner(), $body);
         $body = str_replace('{jdpkgdesc}', \JDPageBuilder\Builder::JDBBanner(), $body);
      } else {
         $body = str_replace('{jdbcomdesc}', \JText::_('COM_JDBUILDER'), $body);
         $body = str_replace('{jdbplgdesc}', \JText::_('PLG_JDBUILDER'), $body);
         $body = str_replace('{jdpkgdesc}', \JText::_('JDBUILDER'), $body);
      }
      $this->app->setBody($body);
   }

   public function onUserAfterDelete($user, $success, $msg)
   {
      $current = JFactory::getUser();
      $deleted = $user['id'];

      $query = "UPDATE `#__jdbuilder_pages` SET `created_by` = '{$current->id}', `modified_by` = '{$current->id}' WHERE `created_by` = '{$deleted}'";
      $db = JFactory::getDbo();
      $db->setQuery($query);
      $db->execute();
   }
}
