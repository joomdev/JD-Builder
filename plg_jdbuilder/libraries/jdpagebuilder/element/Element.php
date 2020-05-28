<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

// No direct access
defined('_JEXEC') or die('Restricted access');

class Element extends BaseElement
{

   public function __construct($object, $parent = null)
   {
      parent::__construct($object, $parent);

      $this->addClass('jdb-element');
      $this->addClass($this->id);
      $this->initPositioning();
      if (isset($this->parent->parent->parent->parent->itemID)) {
         $this->ajaxID = $this->parent->parent->parent->parent->itemID . '$' . $this->parent->parent->parent->parent->itemType . '$' . $this->id;
      } else if (isset($this->parent->parent->parent->parent->parent->parent->itemID)) {
         $this->ajaxID = $this->parent->parent->parent->parent->parent->parent->itemID . '$' . $this->parent->parent->parent->parent->parent->parent->itemType . '$' . $this->id;
      }
   }

   public function ajaxUrl($task)
   {
      return \JURI::root() . 'index.php?option=com_ajax&plugin=jdbuilder&group=system&method=element&format=json&task=' . $this->type . '.' . $task . '&ajaxID=' . $this->ajaxID;
   }

   public function getContent()
   {
      $path = $this->getLayoutPath();
      if (empty($path)) {
         return '';
      }

      if (file_exists($path . '/' . $this->type . '.php')) {
         $layout = new \JLayoutFile($this->type, $path);
      } else {
         $layout = new \JLayoutFile('default', $path);
      }

      if (file_exists($path . '/../helper.php')) {
         require_once($path . '/../helper.php');
      }

      $content = [];

      // Element Content
      $content[] = $layout->render(['element' => $this, 'column' => $this->parent, 'row' => $this->parent->parent, 'section' => $this->parent->parent->parent, 'layout' => $this->parent->parent->parent->parent]);

      // Background Particles
      $content[] = $this->getParticlesBackground();

      // Background Video
      $content[] = $this->getBackgroundVideo();

      return implode("", $content);
   }

   // Helper Functions
   public function getLayoutPath()
   {
      $template = \JFactory::getApplication()->getTemplate(true);
      $template_path = JPATH_THEMES . '/' . $template->template;

      $layout_path = null;

      if (file_exists($template_path . '/html/jdbuilder/' . $this->type . '.php')) {
         $layout_path = $template_path . '/html/jdbuilder/';
      } else if (file_exists($template_path . '/elements/' . $this->type) && file_exists($template_path . '/elements/' . $this->type . '/tmpl/default.php')) {
         $layout_path = $template_path . '/elements/' . $this->type . '/tmpl';
      } else if (file_exists(JDBPATH_COMPONENT . '/elements/' . $this->type) && file_exists(JDBPATH_COMPONENT . '/elements/' . $this->type . '/tmpl/default.php')) {
         $layout_path = JDBPATH_COMPONENT . '/elements/' . $this->type . '/tmpl';
      } else if (file_exists(JDBPATH_ELEMENTS . '/' . $this->type) && file_exists(JDBPATH_ELEMENTS . '/' . $this->type . '/tmpl/default.php')) {
         $layout_path = JDBPATH_ELEMENTS . '/' . $this->type . '/tmpl';
      }

      \JDPageBuilder\Helper::loadLanguage($this->type, JDBPATH_ELEMENTS . '/' . $this->type);

      return $layout_path;
   }

   public function getPath()
   {
      $template = \JFactory::getApplication()->getTemplate(true);
      $template_path = JPATH_THEMES . '/' . $template->template . '/html/jdbuilder';

      $path = null;
      if (file_exists($template_path . '/elements/' . $this->type)) {
         $path = $template_path . '/elements/' . $this->type;
      } else if (file_exists(JDBPATH_COMPONENT . '/elements/' . $this->type)) {
         $path = JDBPATH_COMPONENT . '/elements/' . $this->type;
      } else if (file_exists(JDBPATH_PLUGIN . '/elements/' . $this->type)) {
         $path = JDBPATH_PLUGIN . '/elements/' . $this->type;
      }

      return $path;
   }

   public function getURL()
   {
      $path = $this->getPath();
      $url = str_replace(JPATH_SITE, '', $path);
      return \JURI::root(true) . $url;
   }

   public function renderElement()
   {
      $return = [];
      $content = $this->getElementContent();
      $start = $this->getStart();
      $end = $this->getEnd();


      $return[] = $start;
      $return[] = $content;
      $return[] = $end;

      $return = implode("", $return);
      $this->afterRender();
      return $return;
   }

   public function getElementContent()
   {
      $path = $this->getLayoutPath();
      if (empty($path)) {
         return '';
      }

      $layout = new \JLayoutFile('default', $path);
      return $layout->render(['element' => $this]);
   }

   public function initPositioning()
   {
      $elementPosition = $this->params->get('elementPosition', '');
      if (!empty($elementPosition)) {
         $this->addClass('jdb-position-' . $elementPosition);
         if ($elementPosition == "absolute") {
            $this->parent->addClass('jdb-position-relative');
         }
         $elementPositionHorizontal = $this->params->get('elementPositionHorizontal', 'left');
         $this->addCss($elementPositionHorizontal, 0);
         $elementPositionHOffset = $this->params->get('elementPositionHOffset', null);
         if (!empty($elementPositionHOffset)) {
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
               if (isset($elementPositionHOffset->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($elementPositionHOffset->{$deviceKey})) {
                  $this->addCss($elementPositionHorizontal, $elementPositionHOffset->{$deviceKey}->value . $elementPositionHOffset->{$deviceKey}->unit, $device);
               }
            }
         }

         $elementPositionVertical = $this->params->get('elementPositionVertical', 'top');
         $this->addCss($elementPositionVertical, 0);
         $elementPositionVOffset = $this->params->get('elementPositionVOffset', null);

         if (!empty($elementPositionVOffset)) {
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
               if (isset($elementPositionVOffset->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($elementPositionVOffset->{$deviceKey})) {
                  $this->addCss($elementPositionVertical, $elementPositionVOffset->{$deviceKey}->value . $elementPositionVOffset->{$deviceKey}->unit, $device);
               }
            }
         }
      }

      $elementWidth = $this->params->get('elementWidth', '');
      if (!empty($elementWidth)) {
         if ($elementWidth == 'custom') {
            $width = $this->params->get('elementWidthValue', null);
            if (!empty($width)) {
               foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                  if (isset($width->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
                     $widthVal = '0 0 ' . $width->{$deviceKey}->value . $width->{$deviceKey}->unit;
                     $this->addCss('-ms-flex', $widthVal, $device);
                     $this->addCss('flex', $widthVal, $device);
                     $this->addCss('max-width', $width->{$deviceKey}->value . $width->{$deviceKey}->unit, $device);
                  }
               }
            }
         } else {
            $this->addClass('jdb-element-inline-' . $elementWidth);
         }
      } else {
         $this->addClass('jdb-element-default');
      }
   }
}
