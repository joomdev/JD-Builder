<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use JDPageBuilder\Helpers\ModalHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

extract($displayData);
$builder_assets_path = JURI::root() . 'media/jdbuilder/';

$buiderConfig = JComponentHelper::getParams('com_jdbuilder');

$version = '?' . JDB_MEDIA_VERSION;
if (JDB_DEV) {
   $version = '?' . time();
}
$itemid = JDPageBuilder\Helper::getPageItemIdByLink('index.php?option=com_jdbuilder&view=page&id=' . $id);
$url = !empty($itemid) ? 'index.php?Itemid=' . $itemid : 'index.php?option=com_jdbuilder&view=page&id=' . $id;

$plugin = \JPluginHelper::getPlugin('system', 'jdbuilder');
?>
<input type="hidden" name="<?php echo \JSession::getFormToken(); ?>" value="1" id="joomla-form-token" />
<div id="jdbuilder-area" class="loading<?php echo $enabled ? ' active' : ''; ?>">
   <div id="jdbuilder-app-loader" style="display: flex;flex-direction: column;align-items: center;justify-content: center;height: <?php echo $type == 'page' ? '100vh' : '100%'; ?>;z-index: 99999;position: <?php echo $type == 'page' ? 'fixed' : 'absolute'; ?>;width: <?php echo $type == 'page' ? '100vw' : '100%'; ?>;background: #fff;top: 0;left: 0px;">
      <div style="position: absolute;top:0;left:0;width: 100%;height:100%;z-index: -1;opacity: 0.4;display: none"></div>
      <div id="jdbuilder-apploader-container" style="z-index: 1;max-width: 500px;text-align: center; color: #dadcef;transition: .2s ease-out color;">
         <svg version="1.1" baseProfile="tiny" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="180" viewBox="0 0 1054.447 191.081" xml:space="preserve">
            <g>
               <g>
                  <path fill="currentColor" d="M270.192,186.766V12.57h27.389c15.887,0,27.506,1.027,34.857,3.079
                        c10.434,2.765,18.734,7.896,24.899,15.395c6.166,7.501,9.248,16.342,9.248,26.526c0,6.631-1.402,12.652-4.204,18.059
                        c-2.803,5.409-7.362,10.48-13.677,15.217c10.578,4.974,18.314,11.19,23.21,18.651c4.894,7.46,7.342,16.283,7.342,26.467
                        c0,9.79-2.529,18.71-7.588,26.763c-5.059,8.052-11.577,14.073-19.56,18.059c-7.982,3.987-19.009,5.98-33.074,5.98H270.192z
                        M303.349,44.188v36.71h7.252c8.082,0,14.086-1.697,18.009-5.092c3.923-3.394,5.884-8.012,5.884-13.855
                        c0-5.447-1.863-9.77-5.588-12.967s-9.391-4.796-16.997-4.796H303.349z M303.349,110.503v44.644h8.312
                        c13.774,0,23.057-1.735,27.846-5.21c4.789-3.473,7.185-8.526,7.185-15.158c0-7.499-2.811-13.42-8.432-17.763
                        c-5.621-4.341-14.962-6.513-28.023-6.513H303.349z"></path>
                  <path fill="currentColor" d="M407.204,57.925h32.684v62.052c0,12.079,0.831,20.468,2.492,25.164c1.662,4.698,4.334,8.349,8.014,10.954
                        c3.679,2.605,8.21,3.908,13.593,3.908c5.381,0,9.951-1.282,13.711-3.849c3.76-2.565,6.548-6.335,8.369-11.309
                        c1.345-3.71,2.019-11.644,2.019-23.803V57.925h32.328v54.592c0,22.5-1.776,37.894-5.329,46.184
                        c-4.342,10.106-10.737,17.863-19.184,23.27c-8.448,5.407-19.184,8.112-32.21,8.112c-14.133,0-25.561-3.159-34.283-9.474
                        c-8.724-6.315-14.861-15.117-18.414-26.408c-2.527-7.816-3.789-22.026-3.789-42.631V57.925z"></path>
                  <path fill="currentColor" d="M565.472,4.873c5.667,0,10.527,2.054,14.582,6.158c4.054,4.106,6.082,9.08,6.082,14.921
                        c0,5.764-2.008,10.678-6.023,14.743c-4.015,4.067-8.816,6.099-14.404,6.099c-5.747,0-10.648-2.072-14.702-6.217
                        c-4.055-4.145-6.08-9.177-6.08-15.098c0-5.684,2.005-10.54,6.021-14.566S559.802,4.873,565.472,4.873z M549.307,57.925h32.329
                        v128.841h-32.329V57.925z"></path>
                  <path fill="currentColor" d="M607.452,8.188h32.329v178.577h-32.329V8.188z"></path>
                  <path fill="currentColor" d="M764.239,8.188h32.329v178.577h-32.329v-13.618c-6.31,6-12.638,10.323-18.983,12.967
                        c-6.349,2.644-13.229,3.967-20.641,3.967c-16.638,0-31.029-6.454-43.173-19.362s-18.215-28.954-18.215-48.138
                        c0-19.895,5.875-36.196,17.624-48.907c11.75-12.71,26.021-19.065,42.818-19.065c7.727,0,14.979,1.461,21.762,4.381
                        c6.781,2.921,13.05,7.303,18.808,13.145V8.188z M730.254,84.451c-9.993,0-18.294,3.534-24.903,10.598
                        c-6.609,7.066-9.914,16.125-9.914,27.178c0,11.131,3.362,20.29,10.09,27.473c6.728,7.185,15.008,10.776,24.845,10.776
                        c10.15,0,18.569-3.532,25.256-10.599c6.688-7.064,10.033-16.322,10.033-27.77c0-11.209-3.346-20.289-10.033-27.236
                        C748.941,87.926,740.483,84.451,730.254,84.451z"></path>
                  <path fill="currentColor" d="M958.33,131.7H854.475c1.5,9.159,5.508,16.442,12.02,21.849c6.514,5.409,14.822,8.112,24.928,8.112
                        c12.078,0,22.459-4.223,31.145-12.671l27.236,12.789c-6.791,9.633-14.92,16.757-24.395,21.375
                        c-9.473,4.618-20.723,6.928-33.75,6.928c-20.211,0-36.67-6.375-49.38-19.125c-12.712-12.749-19.065-28.717-19.065-47.901
                        c0-19.658,6.335-35.979,19.006-48.966c12.67-12.986,28.557-19.48,47.665-19.48c20.289,0,36.787,6.494,49.5,19.48
                        c12.709,12.987,19.064,30.138,19.064,51.453L958.33,131.7z M926,106.24c-2.135-7.183-6.346-13.026-12.633-17.526
                        s-13.582-6.75-21.887-6.75c-9.014,0-16.922,2.528-23.723,7.579c-4.27,3.159-8.225,8.724-11.861,16.697H926z"></path>
                  <path fill="currentColor" d="M976.92,57.925h27.711v16.224c3-6.395,6.986-11.25,11.961-14.565c4.973-3.316,10.42-4.974,16.342-4.974
                        c4.184,0,8.564,1.106,13.145,3.315l-10.066,27.829c-3.789-1.895-6.908-2.842-9.355-2.842c-4.973,0-9.176,3.079-12.611,9.237
                        c-3.434,6.158-5.15,18.236-5.15,36.236l0.117,6.276v52.104H976.92V57.925z"></path>
               </g>
               <rect x="70.256" y="57.593" fill="currentColor" width="33.231" height="121.821"></rect>
               <rect x="13.166" y="141.585" fill="currentColor" width="33.231" height="35.818"></rect>
               <rect x="170.567" y="13.425" fill="currentColor" width="33.229" height="165.989"></rect>
               <rect x="13.166" y="153.651" fill="currentColor" width="90.321" height="33.231"></rect>
               <rect x="120.8" y="153.651" fill="currentColor" width="82.996" height="33.231"></rect>
               <rect x="120.8" y="12.417" fill="currentColor" width="82.996" height="33.231"></rect>
               <circle fill="currentColor" cx="87.666" cy="25.807" r="20.898"></circle>
            </g>
         </svg>
         <div class="loading-progress" style="width: 100%;height: 2px;margin: auto;margin-top: 15px;background: #f0ebf7;max-width: 180px;">
            <span id="loading-value" style="background: #6610f2; display: block; height: 2px; width: 0%; transition: .2s ease-out width;box-shadow: 0px 0px 4px 1px rgba(102, 16, 242, 0.20);"></span>
         </div>
         <div id="jdbuilder-apploader-status" style="font-size: 12px;margin-top: 10px;text-align: center;color: #000;"></div>
      </div>
   </div>
   <app-jdbuilder id="jdbuilder"></app-jdbuilder>
   <a href="javascript:void(0);" style="display: none;" id="jdb-export-link"></a>
   <div class="component-version-container">
      <span class="component-version"><?php echo \JText::_('COM_JDBUILDER'); ?> <span>v<?php echo JDB_VERSION; ?></span><?php echo \JText::_('JDBUILDER_VERSION_LABEL'); ?>| Developed with <span style="color: red">&hearts;</span> by <a href="//www.joomdev.com" target="_blank">JoomDev</a></span>
      <div class="support-link">
         <a href="//docs.joomdev.com/category/jd-builder/" target="_blank">Documentation</a> <span>|</span> <a href="//www.joomdev.com/jd-builder/changelog" target="_blank">Changelog</a> <span>|</span> <a href="//www.joomdev.com/forum/jd-builder" target="_blank">Forum</a> <span>|</span> <a href="//www.youtube.com/playlist?list=PLv9TlpLcSZTAnfiT0x10HO5GGaTJhUB1K" target="_blank">Video Tutorials</a> <span>|</span> <a href="//extensions.joomla.org/extension/jd-builder" target="_blank"><span class="icon-joomla"></span> Rate Us</a>
      </div>
   </div>
   <div id="jdb-select2-dropdowns-container"></div>
</div>
<script>
   <?php echo JDPageBuilder\Helper::minifyJS([JPATH_SITE . '/media/jdbuilder/js/admin.js']); ?>
</script>
<script>
   var VERSION = '<?php echo $version; ?>';
   _JDB.DEBUG = <?php echo JDB_DEBUG ? 'true' : 'false'; ?>;
   _JDB.KEY = "<?php echo JDB_KEY; ?>";
   _JDB.GMAPKEY = "<?php echo $buiderConfig->get('gmapkey',  ''); ?>";
   _JDB.FBAPPID = "<?php echo $buiderConfig->get('fbAppId',  ''); ?>";
   _JDB.FAVOURITES = <?php echo \json_encode(JDPageBuilder\Helper::getFavouriteTemplates()); ?>;
   _JDB.SMART_TAGS = <?php echo \json_encode(JDPageBuilder\Helper::getSmartTags()); ?>;

   _JDB.URL = {
      SITEURL: '<?php echo JURI::root(); ?>',
      API: '<?php echo JURI::base(); ?>index.php',
      SITEAPI: '<?php echo JURI::root(); ?>index.php',
      JDAPI: 'https://api.joomdev.com/api/',
      DATA: '<?php echo $builder_assets_path; ?>data/',
      DATAAPI: '<?php echo JURI::base(); ?>index.php?jdb-api=1&task=data',
      ASSETS: '<?php echo $builder_assets_path; ?>/',
      IMAGE: '<?php echo \JURI::root(); ?>images/',
      MEDIA: '<?php echo \JURI::root(); ?>media/jdbuilder/',
      MEDIAPATH: 'images/',
      PREVIEW: '<?php echo JDPageBuilder\Helper::JRouteLink('site', $url); ?>',
      LIVEPREVIEW: '<?php echo JDPageBuilder\Helper::JRouteLink('site', $url . '&jdb-live-preview=1'); ?>',
      GLOBALOPTIONS: 'index.php?option=com_config&view=component&component=com_jdbuilder',
      CONFIG: '<?php echo JURI::base(); ?>index.php?option=com_config&view=component&component=com_jdbuilder'
   };

   _JDB.ICONS = [];
   _JDB.ELEMENTS = [];
   _JDB.FONTS = [];
   _JDB.MENUITEMS = [];

   _JDB.GLOBALSETTINGS = <?php echo JDPageBuilder\Helper::globalSettings()->toString(); ?>;

   _JDB.ANIMATIONS = <?php echo \json_encode(JDPageBuilder\Helper::animationsList()); ?>;

   _JDB.LOGGER = new JDLogger();
   _JDB.LOGGER.debug = _JDB.DEBUG;

   _JDB.LOADER = new JDBAppLoader();
   _JDB.LOADER.value = 25;
   _JDB.LOADER.start();
   _JDB.INFO = {
      "version": "<?php echo JDB_VERSION; ?>"
   };

   var JDBADMIN = true;
   if (typeof $ === 'undefined') {
      var $ = jQuery;
   }
   if ($ !== jQuery) {
      $ = jQuery;
   }

   _JDB.admin = new JDBAdmin();
   _JDB.admin.init();
   _JDB.JDB_CATEGORIES = new Map();
   _JDB.JDB_ARTICLES = new Map();
</script>
<script type="text/javascript" src="<?php echo $builder_assets_path; ?>js/builder/runtime.js<?php echo $version; ?>"></script>
<script type="text/javascript" src="<?php echo $builder_assets_path; ?>js/builder/polyfills.js<?php echo $version; ?>"></script>
<?php if (JDB_DEV) { ?>
   <script type="text/javascript" src="<?php echo $builder_assets_path; ?>js/builder/styles.js<?php echo $version; ?>"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo $builder_assets_path; ?>js/builder/scripts.js<?php echo $version; ?>"></script>
<script type="text/javascript" src="<?php echo $builder_assets_path; ?>js/jdbuilder.min.js<?php echo $version; ?>"></script>
<script>
   JDBRenderer.Helper.baseUrl = '<?php echo JURI::root(); ?>';
</script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/ace/1.4.5/ace.js"></script>
<!-- <script src="<?php echo $builder_assets_path; ?>js/codemirror/codemirror.js<?php echo $version; ?>"></script>
<script src="<?php echo $builder_assets_path; ?>js/codemirror/mode/css/css.js<?php echo $version; ?>"></script>
<script src="<?php echo $builder_assets_path; ?>js/codemirror/mode/javascript/javascript.js<?php echo $version; ?>"></script>
<script src="<?php echo $builder_assets_path; ?>js/codemirror/mode/xml/xml.js<?php echo $version; ?>"></script> -->
<?php if (JDB_DEV) { ?>
   <script type="text/javascript" src="<?php echo $builder_assets_path; ?>js/builder/vendor.js<?php echo $version; ?>"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo $builder_assets_path; ?>js/builder/main.js<?php echo $version; ?>"></script>
<link rel="stylesheet" href="//use.fontawesome.com/releases/v<?php echo JDPageBuilder\Constants::FONTAWESOME_VERSION; ?>/css/all.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.min.css" />
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/typicons/2.0.9/typicons.min.css" />

<?php
if (!empty($buiderConfig->get('gmapkey', ''))) {
   echo '<script async defer src="//maps.googleapis.com/maps/api/js?key=' . $buiderConfig->get('gmapkey', '') . '&libraries=places" type="text/javascript"></script>';
}

echo ModalHelper::selectArticleModal();
?>