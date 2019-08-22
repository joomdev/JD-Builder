<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

class Section extends BaseElement {

   protected $rows = [];
   protected $tag = 'section';

   public function __construct($object, $parent = null) {
      parent::__construct($object, $parent);
      if (isset($object->rows)) {
         foreach ($object->rows as $row) {
            $this->rows[] = new Row($row, $this);
         }
      }
      $this->addClass('jdb-section');
      $this->addClass('jdb-d-flex');
      $this->addClass($this->id);

      // General Options
      $this->initSectionOptions();
   }

   public function getContent() {
      $content = [];
      // Top Shape Divider
      $content[] = $this->getShapeDivider('top');

      // Section Content Container Starts
      $contentWidth = $this->params->get('contentWidth', 'container');
      $contentClass = ['jdb-section-content', 'jdb-d-flex', 'jdb-flex-column'];
      $contentClass[] = $contentWidth == 'container' ? 'jdb-container' : 'jdb-container-fluid';

      // content position
      $contentPosition = $this->params->get('contentPosition', '');
      if (!empty($contentPosition)) {
         $contentClass[] = 'jdb-' . $contentPosition;
      }

      $content[] = '<div class="' . implode(" ", $contentClass) . '">';

      // Section Content
      foreach ($this->rows as $row) {
         $content[] = $row->render();
      }

      // Section Content Container Ends
      $content[] = '</div>';

      // Background Video
      $content[] = $this->getBackgroundVideo();
      // Bottom Shape Divider
      $content[] = $this->getShapeDivider('bottom');

      if ($this->livepreview) {
         //$content[] = '<div onclick="editJDBElement(\'' . $this->id . '\')" class="jdb-settings"></div>';
      }
      return implode("", $content);
   }

   public function getShapeDivider($position = 'top') {

      $type = $this->params->get($position . 'ShapeDivider', '');

      if (empty($type)) {
         return "";
      }

      $height = $this->params->get($position . 'ShapeDividerHeight', 220);

      $color = $this->params->get($position . 'ShapeDividerColor', '#fff');
      $color = empty($color) ? 'transparent' : $color;

      $flip = $this->params->get($position . 'ShapeDividerFlip', false);
      $front = $this->params->get($position . 'ShapeDividerFront', false);

      $this->addCss('position', 'relative');
      $this->addStyle('>*{position:relative;z-index:4;}.jdb-sdivider{height:' . $height . 'px; &.jdb-sdivider-' . $position . ' .jdb-shape-fill{fill: ' . $color . '}}');
      $return[] = '<div class="jdb-sdivider jdb-sdivider-' . $position . '' . ($flip ? ' jdb-sdivider-flip' : '') . '' . ($front ? ' jdb-sdivider-front' : '') . '">';
      $return[] = file_get_contents(JPATH_SITE . '/media/jdbuilder/data/shape-dividers/' . $type . '.svg');
      $return[] = '</div>';
      return implode('', $return);
   }

   public function initSectionOptions() {
      // Section Basic Options
      $this->sectionBasicOptions();

      // Section Layout Options
      $this->sectionLayoutOptions();
   }

   public function sectionBasicOptions() {
      // html tag
      $htmlTag = $this->params->get('htmlTag', '');
      if (!empty($htmlTag)) {
         $this->tag = $htmlTag;
      }
   }

   public function sectionLayoutOptions() {
      $stretchSection = $this->params->get('stretchSection', true);
      if ($stretchSection) {
         $this->addAttribute('jdb-section');
      }

      // content width
      $contentWidth = $this->params->get('contentWidth', 'container');
      if ($contentWidth == 'custom') {
         $contentWidthCustom = $this->params->get('contentWidthCustom', null);
         if (!empty($contentWidthCustom)) {
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
               if (isset($contentWidthCustom->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($contentWidthCustom->{$deviceKey})) {
                  $containerStyle = new ElementStyle('> .jdb-container-fluid');
                  $this->addChildStyle($containerStyle);
                  $containerStyle->addCss('max-width', $contentWidthCustom->{$deviceKey}->value . 'px', $device);
               }
            }
         }
      }

      // section height
      $height = $this->params->get('height', '');
      if (!empty($height)) {
         if ($height == 'screen-fit') {
            $this->addCss('height', '100vh');
         } else {
            $minHeight = $this->params->get('minHeight', NULL);
            if (!empty($minHeight)) {
               foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                  if (isset($minHeight->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($minHeight->{$deviceKey})) {
                     $this->addCss("min-height", $minHeight->{$deviceKey}->value . $minHeight->{$deviceKey}->unit, $device);
                  }
               }
            }
         }
      }
   }

}
