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
    public $faIcons = false, $foundationIcons = false, $typeIcons = false, $materialIcons = false, $animateCss = false;

    public function loadPlugins()
    {
        $this->_loadFontAwesome();
        $this->_loadFoundationIcons();
        $this->_loadTypeIcons();
        $this->_loadMaterialIcons();
        $this->_loadAnimateCss();
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
}
