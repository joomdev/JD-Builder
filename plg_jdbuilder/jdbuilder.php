<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use JDPageBuilder\Field;

// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.helper');

JLoader::registerNamespace('JDPageBuilder', JPATH_PLUGINS . '/system/jdbuilder/libraries/jdpagebuilder', false, false, 'psr4');
JLoader::registerNamespace('JDPageBuilder\\Element', JPATH_PLUGINS . '/system/jdbuilder/libraries/jdpagebuilder/element', false, false, 'psr4');

class plgSystemJDBuilder extends JPlugin
{

   protected $app;
   static $article_layouts = [];

   function __construct(&$subject, $config)
   {
      parent::__construct($subject, $config);

      $stat = stat(JPATH_PLUGINS . '/system/jdbuilder/jdbuilder.php');
      define('JDB_MEDIA_VERSION', md5($stat['mtime']));
      if ($this->app->isAdmin()) {
         $buiderConfig = JComponentHelper::getParams('com_jdbuilder');

         define('JDB_DEBUG', $this->params->get('debug', 0));
         define('JDB_KEY', $buiderConfig->get('key', '', 'RAW'));

         $xml = JFactory::getXML(JPATH_PLUGINS . '/system/jdbuilder/jdbuilder.xml');
         $version = (string) $xml->version;
         define('JDB_VERSION', $version);


         \JDPageBuilder\Helper::loadLanguage();
      }
   }

   // Events 

   public function onAfterDispatch()
   {
      $app = JFactory::getApplication();
      if ($app->input->get('method') == 'onsearchtitles' && $app->input->get('option') == 'com_ajax' && $app->input->get('plugin') == 'jdfinder') {
         $q = $app->input->get('q', '', 'string');
         $this->onSearchTitles($q);
      }

      // Check that we are in the site application.
      if (JFactory::getApplication()->isClient('administrator')) {
         $jdfinderBaseURL = Juri::root() . 'media/jdbuilder/data/finder/data.json';
         $jdfinderdoc = JFactory::getDocument();
         $jdfinderdoc->addStyleSheet(Juri::root() . 'media/jdbuilder/css/jdfinder.css',  ['version' => JDB_MEDIA_VERSION]);
         $jdfinderdoc->addScript(Juri::root() . 'media/jdbuilder/js/jdfinder.js',  ['version' => JDB_MEDIA_VERSION]);
         $jdfinderdoc->addScriptDeclaration(" var jdfinderSearch = '$jdfinderBaseURL';");
      }
   }

   public function onBeforeRender()
   {

      $request = \JDPageBuilder\Builder::request();

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
         // Check that we are in the site application. 
         $this->addFinder();
         $this->addAdminMenu();
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
         $docuemnt->addStyleSheet(JURI::root(true) . '/media/jdbuilder/css/style.min.css', ['version' => JDB_MEDIA_VERSION]);
         $docuemnt->addStyleSheet(JURI::root(true) . '/media/jdbuilder/css/rtl.css', ['version' => JDB_MEDIA_VERSION]);
         $docuemnt->addStyleSheet(JURI::root(true) . '/media/jdbuilder/js/builder/styles.css', ['version' => JDB_MEDIA_VERSION]);
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
      return false;
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
      return false;
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

   public function addFinder()
   {
      $jdfinderpopup = $this->jdFinderModel();
      $buffer = JFactory::getApplication()->getBody();
      $buffer = str_ireplace('</body>', $jdfinderpopup . '</body>', $buffer);
      JFactory::getApplication()->setBody($buffer);
   }

   public function onSearchTitles($q = null)
   {
      //Get the app
      $app       = JFactory::getApplication();
      $db       = JFactory::getDbo();
      $results    =  array();
      $response   = '';

      if (empty($q)) {
         $response .= "<li class='noresult'>" . JText::_('JDFINDER_NO_RESULTS') . "</li>";
         echo new JResponseJson($response);
         $app->close();
      }

      // search results in json file
      $jsonData = json_decode(file_get_contents(JPATH_ROOT . '/media/jdbuilder/data/finder/data.json'), true);
      if (!empty($jsonData)) {
         foreach ($jsonData as $json) {
            $search_result = array_filter($json['keywords'], function ($item) use ($q) {
               if (stripos($item, $q) !== false) {
                  return true;
               }
               return false;
            });

            if (!empty($search_result)) {
               $results['new'][] =  $json;
            }
         }
      }
      // Module Titles
      $db->setQuery("SELECT id,title,module FROM #__modules WHERE `title` LIKE '%$q%'");
      $modules = $db->loadObjectList();

      if (!empty($modules)) {
         foreach ($modules as $k => $module) {
            $results['modules'][$k]['web'] = 'index.php?option=com_modules&task=module.edit&id=' . $module->id;
            $results['modules'][$k]['name'] = $module->title . ' <span class="jdfinder_results_item_description">(' . $module->module . ')</span>';
         }
      }

      // Article Titles
      $db->setQuery("SELECT id,title FROM #__content WHERE `title` LIKE '%$q%'");
      $articles = $db->loadObjectList();

      if (!empty($articles)) {
         foreach ($articles as $k => $article) {
            $results['articles'][$k]['web'] = 'index.php?option=com_content&task=article.edit&id=' . $article->id;
            $results['articles'][$k]['name'] = $article->title;
         }
      }


      // Menu titles & Menu items.
      $db->setQuery("SELECT m.id,m.title,mt.title as menu_name FROM #__menu as m JOIN #__menu_types as mt ON m.menutype = mt.menutype WHERE m.title LIKE '%$q%' AND m.client_id = 0");
      $menu_items  = $db->loadObjectList();

      if (!empty($menu_items)) {
         foreach ($menu_items as $k => $item) {
            $results['menu_items'][$k]['web'] = 'index.php?option=com_menus&task=item.edit&id=' . $item->id;
            $results['menu_items'][$k]['name'] = $item->title . ' <span class="jdfinder_results_item_description">(' . $item->menu_name . ')</span>';
         }
      }
      // Users & Names
      $db->setQuery("SELECT id,name FROM #__users WHERE `name` LIKE '%$q%'");
      $users  = $db->loadObjectList();

      if (!empty($users)) {
         foreach ($users as $k => $item) {
            $results['users'][$k]['web'] = 'index.php?option=com_users&task=user.edit&id=' . $item->id;
            $results['users'][$k]['name'] = $item->name;
         }
      }

      if (!empty($results)) {
         foreach ($results as $key => $val) {
            $type = ucwords(str_replace('_', ' ', $key));
            $response .= '<div class="groupholder"><li><span class="group-label ' . strtolower($type) . '">' . $type . '</span></li>';
            foreach ($val as $k => $v) {
               $web = (isset($v['web'])) ? $v['web'] : '';
               $name = (isset($v['name'])) ? $v['name'] : '';
               $response .= '<li><a href="' . $web . '" target="_blank">' . $name . '</a></li>';
            }
            $response .= '</div>';
         }
      } else {
         $response .= "<li class='noresult'>" . JText::_('JDFINDER_NO_RESULTS') . "</li>";
      }
      echo new JResponseJson($response);
      $app->close();
   }
   public function jdFinderModel()
   {
      return '<div id="jdfinderUnderlay" class="jdfinder-underlay">
		<div class="modal-outer">
				<div id="helpModal" class="jdfinder-modal">
					<div class="jdfinder-header">
						<div class="jdfinder-name"><img class="jdfinder-icon" src="' . Juri::root() . 'media/jdbuilder/images/jdb-icon.svg"><span class="jdfinder-text">' . JText::_("JDFINDER") . '</span></div>
						<div id="helpClose" class="jdfinder-close">&times;</div>
					</div>
						<div id="helpModalContent" class="jdfinder-modal-content">
						   <div id="helpListWrap" class="jdfinder-list-wrap">  
							<div class="search-wrapper">
								<input type="text" name="jdfindersearch" id="jdfindersearch" placeholder="' . JText::_('JDFINDER_TYPE_TO_FIND_ANYTHING') . '" class="search" />
								<i class="icon-search jdfinder-search-icon" aria-hidden="true"></i>
							</div>
							<ul class="jdfinder_results">
							</ul>
						   </div>
					</div>
				</div>
			</div>
		</div>';
   }
   public function addAdminMenu()
   {
      $adminMenu = '<ul class="nav"><li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">' . \JText::_('COM_JDBUILDER') . ' <span class="caret"></span></a><ul class="dropdown-menu scroll-menu"><li><a class="no-dropdown"  href="index.php?option=com_jdbuilder&view=pages">' . \JText::_('COM_JDBUILDER_TITLE_PAGES') . '</a></li><li><a class="no-dropdown"  href="index.php?option=com_categories&extension=com_jdbuilder.pages">' . \JText::_('JCATEGORIES') . '</a></li></ul></li></ul>';
      $body = $this->app->getBody();
      $body = str_replace('<ul id="nav-empty"', $adminMenu . '<ul id="nav-empty"', $body);
      $this->app->setBody($body);
   }
}
