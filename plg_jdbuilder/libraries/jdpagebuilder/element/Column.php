<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

class Column extends BaseElement {

   protected $elements = [];
   protected $size;

   public function __construct($object, $parent = null) {
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

   public function getContent() {
      $content = [];

      // Column Content
      foreach ($this->elements as $element) {
         $content[] = $element->render();
      }

      // Background Video
      $content[] = $this->getBackgroundVideo();

      if ($this->livepreview) {
         //$content[] = '<div class="jdb-settings" data-element-id="' . $this->id . '"></div>';
      }

      return implode("", $content);
   }

   public function initColumnOptions() {
      // Basic Options
      $this->columnBasicOptions();
      // Layout Options
      $this->columnLayoutOptions();
      // Responsive Options
      $this->columnResponsiveOptions();
   }

   public function columnBasicOptions() {
      // html tag
      $htmlTag = $this->params->get('htmlTag', '');
      if (!empty($htmlTag)) {
         $this->tag = $htmlTag;
      }
   }

   public function columnLayoutOptions() {
      // content position
      $verticalContentPosition = $this->params->get('verticalContentPosition', '');
      if (!empty($verticalContentPosition)) {
         $this->addClass('jdb-' . $verticalContentPosition);
      }
      $horizontalContentPosition = $this->params->get('horizontalContentPosition', '');
      if (!empty($horizontalContentPosition)) {
         $this->addClass('jdb-' . $horizontalContentPosition);
      }

      $spaceBetween = $this->params->get('spaceBetweenElements', null);

      if (!empty($spaceBetween)) {
         $elementStyle = new ElementStyle('> .jdb-element:not(:last-child)');
         $this->addChildStyle($elementStyle);
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($spaceBetween->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($spaceBetween->{$deviceKey})) {
               $elementStyle->addCss("margin-bottom", $spaceBetween->{$deviceKey}->value . "px", $device);
            }
         }
      }
   }

   public function columnResponsiveOptions() {
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
