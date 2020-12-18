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

class Column extends BaseElement
{

   public $elements = [];
   protected $size;

   public function __construct($object, $parent = null)
   {
      parent::__construct($object, $parent);
      if (isset($object->elements)) {
         foreach ($object->elements as $element) {
            if ($element->type == 'inner-row') {
               $this->elements[] = new Row($element, $this, true);
            } else {
               $this->elements[] = new Element($element, $this);
            }
         }
      }
      $this->size = $object->size;
      $this->addClass('jdb-column');
      $this->addClass('jdb-col-lg-' . $this->size);
      $this->addClass($this->id);

      $this->initColumnOptions();
   }

   public function getContent()
   {
      $innerStyle = new ElementStyle('.jdb-column-inner');
      $this->addChildStyle($innerStyle);
      $innerClass = [];
      // content position
      $verticalContentPosition = $this->params->get('verticalContentPosition', null);
      if ($verticalContentPosition != null && $verticalContentPosition != '') {
         if (is_string($verticalContentPosition)) {
            $position = \json_decode('{"md":"' . $verticalContentPosition . '","sm":"' . $verticalContentPosition . '","xs": "' . $verticalContentPosition . '"}', false);
            $verticalContentPosition = $position;
         }

         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($verticalContentPosition->{$deviceKey})) {
               $vValue = '';
               switch ($verticalContentPosition->{$deviceKey}) {
                  case 'align-content-start':
                     $vValue = 'flex-start';
                     break;
                  case 'align-content-center':
                     $vValue = 'center';
                     break;
                  case 'align-content-end':
                     $vValue = 'flex-end';
                     break;
                  case 'align-content-around':
                     $vValue = 'space-around';
                     break;
                  case 'align-content-between':
                     $vValue = 'space-between';
                     break;
               }
               $innerStyle->addCss('align-content', $vValue, $device);
            }
         }
      }


      $horizontalContentPosition = $this->params->get('horizontalContentPosition', null);
      if ($horizontalContentPosition != null && $horizontalContentPosition != '') {
         if (is_string($horizontalContentPosition)) {
            $position = \json_decode('{"md":"' . $horizontalContentPosition . '","sm":"' . $horizontalContentPosition . '","xs": "' . $horizontalContentPosition . '"}', false);
            $horizontalContentPosition = $position;
         }

         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($horizontalContentPosition->{$deviceKey})) {
               $hValue = '';
               switch ($horizontalContentPosition->{$deviceKey}) {
                  case 'justify-content-start':
                     $hValue = 'flex-start';
                     break;
                  case 'justify-content-center':
                     $hValue = 'center';
                     break;
                  case 'justify-content-end':
                     $hValue = 'flex-end';
                     break;
                  case 'justify-content-around':
                     $hValue = 'space-around';
                     break;
                  case 'justify-content-between':
                     $hValue = 'space-between';
                     break;
               }
               $innerStyle->addCss('justify-content', $hValue, $device);
            }
         }
      }

      $content = ['<div class="jdb-column-inner' . (!empty($innerClass) ? ' ' . implode(' ', $innerClass) : '') . '">'];

      // Column Content
      foreach ($this->elements as $element) {
         $content[] = $element->render();
      }

      $content[] = '</div>';

      // Background Particles
      $content[] = $this->getParticlesBackground();

      // Background Video
      $content[] = $this->getBackgroundVideo();

      return implode("", $content);
   }

   public function initColumnOptions()
   {
      // Basic Options
      $this->columnBasicOptions();
      // Layout Options
      $this->columnLayoutOptions();
      // Responsive Options
      $this->columnResponsiveOptions();
   }

   public function columnBasicOptions()
   {
      // html tag
      $htmlTag = $this->params->get('htmlTag', '');
      if (!empty($htmlTag)) {
         $this->tag = $htmlTag;
      }
   }

   public function columnLayoutOptions()
   {
      $spaceBetween = $this->params->get('spaceBetweenElements', null);

      if (!empty($spaceBetween)) {
         $elementStyle = new ElementStyle('.jdb-element.jdb-element-default:not(:last-child)');
         $elementStyle2 = new ElementStyle('.jdb-element:not(:last-child):not(.jdb-element-default)');
         $this->addChildStyle($elementStyle);
         $this->addChildStyle($elementStyle2);
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($spaceBetween->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($spaceBetween->{$deviceKey})) {
               $elementStyle->addCss("margin-bottom", $spaceBetween->{$deviceKey}->value . "px", $device);
               $elementStyle2->addCss("margin-right", $spaceBetween->{$deviceKey}->value . "px", $device);
            }
         }
      }
   }

   public function columnResponsiveOptions()
   {
      // content position
      $columnSizeTablet = $this->params->get('columnSizeTablet', '');
      if (!empty($columnSizeTablet)) {
         $this->addClass('jdb-col-md-' . $columnSizeTablet);
      }
      $columnSizeMobile = $this->params->get('columnSizeMobile', '');
      if (!empty($columnSizeMobile)) {
         $this->addClass('jdb-col-' . $columnSizeMobile);
      }
   }
}
