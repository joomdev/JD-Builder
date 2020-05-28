<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Helpers;

// No direct access
defined('_JEXEC') or die('Restricted access');

class Document
{
    public $faIcons = false, $foundationIcons = false, $typeIcons = false, $materialIcons = false, $animateCss = false, $lightBox = false, $googleMap = false, $googleMapCallbacks = [];

    public function loadPlugins()
    {
        $this->_loadFontAwesome();
        $this->_loadFoundationIcons();
        $this->_loadTypeIcons();
        $this->_loadMaterialIcons();
        $this->_loadAnimateCss();
        $this->_loadGoogleMap();
        $this->_loadLightBox();
    }

    private function _loadFontAwesome()
    {
        if (!$this->faIcons) return;
        $document = \JFactory::getDocument();
        $document->addStylesheet('//use.fontawesome.com/releases/v' . \JDPageBuilder\Constants::FONTAWESOME_VERSION . '/css/all.css');
    }

    private function _loadFoundationIcons()
    {
        if (!$this->foundationIcons) return;
        $document = \JFactory::getDocument();
        $document->addStylesheet('//cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.min.css');
    }

    private function _loadTypeIcons()
    {
        if (!$this->typeIcons) return;
        $document = \JFactory::getDocument();
        $document->addStylesheet('//cdnjs.cloudflare.com/ajax/libs/typicons/2.0.9/typicons.min.css');
    }

    private function _loadMaterialIcons()
    {
        if (!$this->materialIcons) return;
        $document = \JFactory::getDocument();
        $document->addStylesheet('//fonts.googleapis.com/icon?family=Material+Icons');
    }

    private function _loadAnimateCss()
    {
        if (!$this->animateCss) return;
        $document = \JFactory::getDocument();
        $document->addStylesheet('//cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.0/animate.min.css');
    }

    private function _loadLightBox()
    {
        if (!$this->lightBox) return;
        $document = \JFactory::getDocument();
        $document->addScript(\JURI::root() . 'media/jdbuilder/js/jdlightbox.min.js');
    }

    private function _loadGoogleMap()
    {
        if (!$this->googleMap) return;
        $buiderConfig = \JComponentHelper::getParams('com_jdbuilder');
        if (!empty($buiderConfig->get('gmapkey', ''))) {
            $callbacks = [];
            foreach ($this->googleMapCallbacks as $func) {
                $callbacks[] = $func . '()';
            }
            $document = \JFactory::getDocument();
            $script = '<script src="https://maps.googleapis.com/maps/api/js?key=' . $buiderConfig->get('gmapkey', '') . '&callback=initJDGoogleMaps" async defer></script><script>var _JDGoogleMapsLoaded = false; function initJDGoogleMaps(){ if(_JDGoogleMapsLoaded){return false;} try{ ' . implode(';', $callbacks) . '; _JDGoogleMapsLoaded = true; }catch(e){ setTimeout(function(){ initJDGoogleMaps(); }, 200); }}</script>';
            $document->addCustomTag($script);
        }
    }
}
