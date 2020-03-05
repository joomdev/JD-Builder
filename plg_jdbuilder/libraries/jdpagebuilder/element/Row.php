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

class Row extends BaseElement
{

   public $columns = [];
   public $inner = false;

   public function __construct($object, $parent = null, $inner = false)
   {
      parent::__construct($object, $parent);
      $this->inner = $inner;
      if (isset($object->cols)) {
         foreach ($object->cols as $column) {
            $this->columns[] = new Column($column, $this);
         }
      }

      $this->addClass('jdb-row');
      if ($this->inner) {
         $this->addClass('jdb-inner-row');
      }
      $this->addClass($this->id);

      // Row Options
      $this->initRowOptions();
   }

   public function getContent()
   {
      $content = [];

      // Row Content
      foreach ($this->columns as $column) {
         $content[] = $column->render();
      }

      // Background Particles
      $content[] = $this->getParticlesBackground();

      // Background Video
      $content[] = $this->getBackgroundVideo();

      return implode("", $content);
   }

   public function getStart()
   {
      $return = [];
      $return[] = '<div id="' . $this->id . '"' . $this->getAttrs() . '>';
      return implode("", $return);
   }

   public function getEnd()
   {
      $return = [];
      $return[] = '</div>';
      return implode("", $return);
   }

   public function initRowOptions()
   {
      // Row Layout Options
      $this->rowLayoutOptions();
   }

   public function rowLayoutOptions()
   {
      $this->rowGutterOptions();
      $this->rowColumnDirectionOptions();
   }

   public function rowGutterOptions()
   {
      $guttersType = $this->params->get('guttersType', "");
      if (empty($guttersType)) {
         return;
      }
      if ($guttersType == "none") {
         $this->addClass('jdb-no-gutters');
         return;
      } else {
         $gutter = $this->params->get('guttersSize', null);
         if (!empty($gutter)) {
            $colStyle = new ElementStyle('> .jdb-column');
            $this->addChildrenStyle([$colStyle]);
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
               if (isset($gutter->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($gutter->{$deviceKey})) {
                  $colStyle->addCss("padding-left", $gutter->{$deviceKey}->value . "px", $device);
                  $colStyle->addCss("padding-right", $gutter->{$deviceKey}->value . "px", $device);
               }
            }
         }
      }
   }

   public function rowColumnDirectionOptions()
   {
      $columnReverseMedium = $this->params->get('columnReverseMedium', false);
      $columnReverseSmall = $this->params->get('columnReverseSmall', false);
      if ($columnReverseMedium || $columnReverseSmall) {
         $this->addClass('jdb-d-flex');
         if ($columnReverseSmall && !$columnReverseMedium) {
            $this->addClass('jdb-flex-column-reverse');
            $this->addClass('jdb-flex-md-row');
         } else if (!$columnReverseSmall && $columnReverseMedium) {
            $this->addClass('jdb-flex-md-column-reverse');
            $this->addClass('jdb-flex-lg-row');
         } else {
            $this->addClass('jdb-flex-column-reverse');
            $this->addClass('jdb-flex-md-column-reverse');
            $this->addClass('jdb-flex-lg-row');
         }
      }
   }
}
