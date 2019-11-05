<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

// No direct access
defined('_JEXEC') or die('Restricted access');

class Row extends BaseElement
{

   protected $columns = [];
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

      if ($this->livepreview) {
         //$content[] = '<div class="jdb-settings" data-element-id="' . $this->id . '"></div>';
      }
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
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
               if (isset($gutter->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($gutter->{$deviceKey})) {
                  $css = ".jdb-col,.jdb-col-1,.jdb-col-10,.jdb-col-11,.jdb-col-12,.jdb-col-2,.jdb-col-3,.jdb-col-4,.jdb-col-5,.jdb-col-6,.jdb-col-7,.jdb-col-8,.jdb-col-9,.jdb-col-auto,.jdb-col-lg,.jdb-col-lg-1,.jdb-col-lg-10,.jdb-col-lg-11,.jdb-col-lg-12,.jdb-col-lg-2,.jdb-col-lg-3,.jdb-col-lg-4,.jdb-col-lg-5,.jdb-col-lg-6,.jdb-col-lg-7,.jdb-col-lg-8,.jdb-col-lg-9,.jdb-col-lg-auto,.jdb-col-md,.jdb-col-md-1,.jdb-col-md-10,.jdb-col-md-11,.jdb-col-md-12,.jdb-col-md-2,.jdb-col-md-3,.jdb-col-md-4,.jdb-col-md-5,.jdb-col-md-6,.jdb-col-md-7,.jdb-col-md-8,.jdb-col-md-9,.jdb-col-md-auto,.jdb-col-sm,.jdb-col-sm-1,.jdb-col-sm-10,.jdb-col-sm-11,.jdb-col-sm-12,.jdb-col-sm-2,.jdb-col-sm-3,.jdb-col-sm-4,.jdb-col-sm-5,.jdb-col-sm-6,.jdb-col-sm-7,.jdb-col-sm-8,.jdb-col-sm-9,.jdb-col-sm-auto,.jdb-col-xl,.jdb-col-xl-1,.jdb-col-xl-10,.jdb-col-xl-11,.jdb-col-xl-12,.jdb-col-xl-2,.jdb-col-xl-3,.jdb-col-xl-4,.jdb-col-xl-5,.jdb-col-xl-6,.jdb-col-xl-7,.jdb-col-xl-8,.jdb-col-xl-9,.jdb-col-xl-auto{padding-right:" . ($gutter->{$deviceKey}->value) . "px;padding-left:" . ($gutter->{$deviceKey}->value) . "px}";
                  $this->addStyle($css, $device);
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
