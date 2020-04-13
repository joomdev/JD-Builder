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

class Section extends BaseElement
{

   public $rows = [];
   protected $tag = 'section';

   public function __construct($object, $parent = null)
   {
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

   public function getContent()
   {
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

      // Background Particles
      $content[] = $this->getParticlesBackground();

      // Background Video
      $content[] = $this->getBackgroundVideo();
      // Bottom Shape Divider
      $content[] = $this->getShapeDivider('bottom');

      return implode("", $content);
   }

   public function getShapeDivider($position = 'top')
   {

      $type = $this->params->get($position . 'ShapeDivider', '');

      if (empty($type)) {
         return "";
      }

      // $height = $this->params->get($position . 'ShapeDividerHeight', \json_decode('{value:220}', false));

      $color = $this->params->get($position . 'ShapeDividerColor', '#fff');
      $color = empty($color) ? 'transparent' : $color;

      $flip = $this->params->get($position . 'ShapeDividerFlip', false);
      $front = $this->params->get($position . 'ShapeDividerFront', false);

      $this->addClass('jdb-has-shapedivider');

      $sDividerStyle = new ElementStyle('.jdb-sdivider[data-position=' . $position . ']' . ' svg');
      $sDividerShapeStyle = new ElementStyle('.jdb-sdivider[data-position=' . $position . ']' . ' .jdb-shape-fill');

      $this->addChildrenStyle([$sDividerStyle, $sDividerShapeStyle]);

      $sDividerShapeStyle->addCss('fill', $color);

      $height = $this->params->get($position . 'ShapeDividerHeight', null);
      if (!empty($height)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($height->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($height->{$deviceKey})) {
               $sDividerStyle->addCss('height', $height->{$deviceKey}->value . 'px', $device);
            }
         }
      }

      $width = $this->params->get($position . 'ShapeDividerWidth', null);
      if (!empty($width)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($width->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
               $sDividerStyle->addCss('width', 'calc(' . $width->{$deviceKey}->value . '% + 1.3px)', $device);
            }
         }
      }

      $return[] = '<div class="jdb-sdivider jdb-sdivider-' . $type . '' . ($front ? ' jdb-sdivider-front' : '') . '" data-position="' . $position . '"' . ($flip ? ' data-flip' : '') . '>';
      $return[] = file_get_contents(JPATH_SITE . '/media/jdbuilder/data/shape-dividers/' . $type . '.svg');
      $return[] = '</div>';
      return implode('', $return);
   }

   public function initSectionOptions()
   {
      // Section Basic Options
      $this->sectionBasicOptions();

      // Section Layout Options
      $this->sectionLayoutOptions();
   }

   public function sectionBasicOptions()
   {
      // html tag
      $htmlTag = $this->params->get('htmlTag', '');
      if (!empty($htmlTag)) {
         $this->tag = $htmlTag;
      }
   }

   public function sectionLayoutOptions()
   {
      $stretchSection = $this->params->get('stretchSection', false);
      if ($stretchSection) {
         $this->addAttribute('jdb-section', 'stretch:' . ($stretchSection ? 'true' : 'false'));
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
                  $containerStyle->addCss('max-width', $contentWidthCustom->{$deviceKey}->value . $contentWidthCustom->{$deviceKey}->unit, $device);
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
