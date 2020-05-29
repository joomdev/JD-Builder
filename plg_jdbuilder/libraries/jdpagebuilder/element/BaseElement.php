<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

use JDPageBuilder\Builder;
use JDPageBuilder\Helper;

// No direct access
defined('_JEXEC') or die('Restricted access');

class BaseElement
{

   public $id;
   public $params;
   public $type;
   public $visibility = true;
   protected $tag = 'div';
   protected $classes = [];
   protected $attributes = [];
   public $parent;
   public $authorised = false;
   public $style;
   public $childStyles = [];
   public $indexMode = false;

   public function __construct($object, $parent = null, $indexMode = false)
   {
      $this->id = isset($object->id) ? $object->id : null;
      $this->indexMode = $parent !== null ? $parent->indexMode : $indexMode;
      $this->parent = $parent;
      if (isset($object->type)) {
         $this->type = $object->type;
      }
      if (isset($object->visibility)) {
         $this->visibility = $object->visibility;
      }
      $params = new \JRegistry();
      if (isset($object->params)) {
         $params->loadObject($object->params);
      }
      $this->params = $params;
      if (!empty($this->id)) {
         // custom id
         $custom_id = $this->params->get('custom_id', '');
         if (!empty($custom_id)) {
            $this->id = $custom_id;
         }
         $this->style = new ElementStyle('#' . $this->id);
      }
      // Design Options
      $this->initDesignOptions();

      // Advanced Options
      $this->initAdvancedOptions();
   }

   public function getStart()
   {
      return '<' . $this->tag . ' id="' . $this->id . '"' . $this->getAttrs() . '>';
   }

   public function getEnd()
   {
      return '</' . $this->tag . '>';
   }

   public function getContent()
   {
      return $this->id;
   }

   public function render($output = false)
   {
      if (!$this->authorised) {
         return "";
      }

      if (!$this->visibility) {
         if ($output) {
            echo "";
         } else {
            return "";
         }
      }
      $return = [];
      $content = $this->getContent();
      $start = $this->getStart();
      $end = $this->getEnd();


      $return[] = $start;
      $return[] = $content;
      $return[] = $end;

      $return = implode("", $return);
      $return = Helper::renderHTML($return, $this->indexMode);
      $this->afterRender();
      if ($output) {
         echo $return;
      } else {
         return $return;
      }
   }

   public function afterRender()
   {
      if (empty($this->type)) {
         return;
      }
      $this->style->render();
      foreach ($this->childStyles as $childStyle) {
         $childStyle->render();
      }
   }

   public function getAttrs()
   {
      $return = [];

      if (!empty($this->classes)) {
         $return[] = 'class="' . implode(" ", $this->classes) . '"';
      }

      if (!empty($this->attributes)) {
         foreach ($this->attributes as $attribute => $attributeValue) {
            $return[] = $attribute . '="' . $attributeValue . '"';
         }
      }

      return implode(' ', $return);
   }

   public function addClass($class)
   {
      $this->classes[] = $class;
   }

   public function addAttribute($attribute, $value = '')
   {
      $this->attributes[$attribute] = $value;
   }

   public function dataAttr($property, $value = "")
   {
      $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
      $this->attributes['data-' . $property] = $value;
   }

   public function addCss($property, $value, $device = "desktop")
   {
      if (empty($this->style)) {
         return;
      }
      $this->style->addCss($property, $value, $device);
   }

   public function addStyle($css, $device = "desktop")
   {
      if (empty($this->style)) {
         return;
      }
      $this->style->addStyle($css, $device);
   }

   public function addChildStyle(ElementStyle $childStyle)
   {
      if (explode(',', $childStyle->selector) > 1) {
         $selector = [];
         foreach (explode(',', $childStyle->selector) as $childselector) {
            $glue = " ";
            if (substr($childselector, 0, 2) === '::') {
               $glue = "";
            }
            if (substr($childselector, 0, 1) == '<') {
               $selector[] = substr($childselector, 1);
            } else {
               if (substr($childselector, 0, 7) === '::hover' || substr($childselector, 0, 7) === '::active' || substr($childselector, 0, 7) === '::focus') {
                  $childselector = substr($childselector, 1);
               }
               $selector[] = $this->style->selector . $glue . $childselector;
            }
         }
         $childStyle->selector = implode(',', $selector);
      } else {
         $glue = " ";
         if (substr($childStyle->selector, 0, 2) === '::') {
            $glue = "";
         }

         if (substr($childStyle->selector, 0, 1) == '<') {
            $childStyle->selector = substr($childStyle->selector, 1);
         } else {
            if (substr($childStyle->selector, 0, 7) === '::hover' || substr($childStyle->selector, 0, 7) === '::active' || substr($childStyle->selector, 0, 7) === '::focus') {
               $childStyle->selector = substr($childStyle->selector, 1);
            }

            $childStyle->selector = $this->style->selector . $glue . $childStyle->selector;
         }
      }
      $this->childStyles[] = $childStyle;
   }

   public function addChildrenStyle($childStyles = [])
   {
      foreach ($childStyles as $childStyle) {
         $this->addChildStyle($childStyle);
      }
   }

   public function addStylesheet($file)
   {
      \JDPageBuilder\Builder::addStylesheet($file);
   }

   public function addScript($js)
   {
      \JDPageBuilder\Builder::addScript($js);
   }

   public function addJavascript($file)
   {
      \JDPageBuilder\Builder::addJavascript($file);
   }

   public function getBackgroundVideo()
   {
      $background = $this->params->get('background', 'none');
      $html = '';
      if ($background == "video") {
         $link = $this->params->get('backgroundVideoMedia', '');
         if (!empty($link)) {
            $this->addClass('jdb-has-video-background');
            $html = '<div jdb-video-background data-src="' . \JDPageBuilder\Helper::mediaValue($link) . '"></div>';
         }
      }
      return $html;
   }

   public function getParticlesBackground()
   {
      $background = $this->params->get('particlesBackground', false);
      $return = [];

      if ($background) {

         $type = $this->params->get('particlesType', 'presets');
         $color = $this->params->get('particlesColor', '');
         $backgroundColor = $this->params->get('backgroundColor', '');
         $background = $this->params->get('background', 'none');
         $particlesShape = $this->params->get('particlesShape', '');

         if ($type == 'presets' && $color == '' && ($background == 'none' || ($background == 'color' && $backgroundColor == '')) && $particlesShape != 'image') {
            $this->addCss('background-color', '#073366');
         }

         if ($type == 'presets') {
            $preset = $this->params->get('particlesPreset', '');

            if (file_exists(JPATH_SITE . '/media/jdbuilder/data/particles-presets/' . $preset . '.json')) {
               $params = file_get_contents(JPATH_SITE . '/media/jdbuilder/data/particles-presets/' . $preset . '.json');
            } else {
               $params = file_get_contents(JPATH_SITE . '/media/jdbuilder/data/particles-presets/default.json');
            }

            $params = \json_decode($params, true);

            $size = $this->params->get('particlesSize', null);
            if (\JDPageBuilder\Helper::checkSliderValue($size)) {
               $params['particles']['size']['value'] = $size->value;
            }

            $count = $this->params->get('particlesCount', null);
            if (\JDPageBuilder\Helper::checkSliderValue($count)) {
               $params['particles']['number']['value'] = $count->value;
            }

            $speed = $this->params->get('particlesSpeed', null);
            if (\JDPageBuilder\Helper::checkSliderValue($speed)) {
               $params['particles']['move']['speed'] = $speed->value;
            }

            $direction = $this->params->get('particlesDirection', '');
            if ($direction != '') {
               $params['particles']['move']['direction'] = $direction;
            }

            $opacity = $this->params->get('particlesOpacity', null);
            if (\JDPageBuilder\Helper::checkSliderValue($opacity)) {
               $params['particles']['opacity']['value'] = ($opacity->value / 100);
            }

            if ($color != '') {
               $params['particles']['color']['value'] = $color;
            }

            $link = $this->params->get('particlesLink', '');
            if ($link == 'custom') {
               $params['particles']['line_linked']['enable'] = true;
               $linkOpacity = $this->params->get('particlesLinkOpacity', null);
               if (\JDPageBuilder\Helper::checkSliderValue($linkOpacity)) {
                  $params['particles']['line_linked']['opacity'] = ($linkOpacity->value / 100);
               }
               $linkDistance = $this->params->get('particlesLinkDistance', null);
               if (\JDPageBuilder\Helper::checkSliderValue($linkDistance)) {
                  $params['particles']['line_linked']['distance'] = $linkDistance->value;
               }
               $linkColor = $this->params->get('particlesLinkColor', '');
               if ($linkColor != '') {
                  $params['particles']['line_linked']['color'] = $linkColor;
               }
               $params['particles']['line_linked']['width'] = 1;
            } else if ($link == 'none') {
               $params['particles']['line_linked']['enable'] = false;
            }

            $particlesImage = $this->params->get('particlesImage', '');
            if ($particlesShape != '') {
               $params['particles']['shape']['type'] = $particlesShape;
               if ($particlesShape == 'image' && $particlesImage != '') {
                  $params['particles']['shape']['image'] = [
                     'src' => \JDPageBuilder\Helper::mediaValue($particlesImage),
                     'width' => $params['particles']['size']['value'],
                     'height' => $params['particles']['size']['value']
                  ];
               } else if ($particlesShape == 'image') {
                  $params['particles']['shape']['image'] = [
                     'src' => ''
                  ];
               }
            }

            $params['interactivity'] = [
               'events' => [
                  'onhover' => [
                     'enable' => false
                  ],
                  'onclick' => [
                     'enable' => false
                  ],
                  'resize' => true
               ]
            ];
         } else {
            $particlesCustom = $this->params->get('particlesCustom', '');
            if ($particlesCustom != '' && \JDPageBuilder\Helper::isValidJSON($particlesCustom)) {
               $params = \json_decode($particlesCustom, true);
            } else {
               return '';
            }
         }

         $this->addClass('jdb-has-particles-background');
         $return[] = '<div class="jdb-particles-background"><div id="jdb-particles-' . $this->id . '"></div></div>';

         Builder::loadParticleJS('jdb-particles-' . $this->id, $params);
      }

      return implode("", $return);
   }

   // Basic Options
   public function initDesignOptions()
   {
      // Basic Options
      $this->basicOptions();
      // Background Options
      $this->backgroundOptions();
      // Background Overlay Options
      $this->backgroundOverlayOptions();
      // Border Options
      $this->borderOptions();
      // Spacing Options
      $this->spacingOptions();
      // Typography Options
      $this->typographyOptions();
      // Custom CSS Options
      $this->customCssOptions();
   }

   public function basicOptions()
   {
      // custom class
      $custom_class = $this->params->get('custom_class', '');
      if (!empty($custom_class)) {
         $this->addClass($custom_class);
      }
   }

   public function backgroundOptions()
   {
      $background = $this->params->get('background', 'none');
      switch ($background) {
         case "color":
            $backgroundColor = $this->params->get('backgroundColor', '');
            if (!empty($backgroundColor)) {
               $this->addCss('background-color', $backgroundColor);
            }
            break;
         case "image":
            $backgroundColor = $this->params->get('backgroundColor', '');
            if (!empty($backgroundColor)) {
               $this->addCss('background-color', $backgroundColor);
            }
            $backgroundImage = $this->params->get('backgroundImage', '');
            if (!empty($backgroundImage)) {
               $this->addCss('background-image', 'url(' . \JDPageBuilder\Helper::mediaValue($backgroundImage) . ')');
               $backgroundRepeat = $this->params->get('backgroundRepeat', '');
               if (!empty($backgroundRepeat)) {
                  $this->addCss('background-repeat', $backgroundRepeat);
               }
               $backgroundSize = $this->params->get('backgroundSize', '');
               if (!empty($backgroundSize)) {
                  if ($backgroundSize != 'custom') {
                     $this->addCss('background-size', $backgroundSize);
                  } else {
                     $width = $this->params->get('backgroundWidth', null);
                     if (!empty($width)) {
                        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                           if (isset($width->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
                              $this->addCss('background-size', $width->{$deviceKey}->value . $width->{$deviceKey}->unit, $device);
                           }
                        }
                     }
                  }
               }
               $backgroundAttachment = $this->params->get('backgroundAttachment', '');
               if (!empty($backgroundAttachment)) {
                  $this->addCss('background-attachment', $backgroundAttachment);
               }
               $backgroundPosition = $this->params->get('backgroundPosition', '');
               if (!empty($backgroundPosition)) {
                  if ($backgroundPosition != 'custom') {
                     $this->addCss('background-position', $backgroundPosition);
                  } else {
                     $position = $this->params->get('backgroundXPosition', null);
                     if (!empty($position)) {
                        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                           if (isset($position->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($position->{$deviceKey})) {
                              $this->addCss('background-position-x', $position->{$deviceKey}->value . $position->{$deviceKey}->unit, $device);
                           }
                        }
                     }
                     $position = $this->params->get('backgroundYPosition', null);
                     if (!empty($position)) {
                        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                           if (isset($position->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($position->{$deviceKey})) {
                              $this->addCss('background-position-y', $position->{$deviceKey}->value . $position->{$deviceKey}->unit, $device);
                           }
                        }
                     }
                  }
               }
            }
            break;
         case 'gradient':
            $backgroundGradient = $this->params->get('backgroundGradient', '');
            if (!empty($backgroundGradient)) {
               $this->addCss('background-image', $backgroundGradient);
            }
            break;
      }
   }

   public function backgroundOverlayOptions()
   {
      $background = $this->params->get('background', 'none');
      if ($background == "image" || $background == "video") {
         $backgroundOverlayColor = $this->params->get('backgroundOverlayColor', '');
         $backgroundOverlayImage = $this->params->get('backgroundOverlayImage', '');
         if (!empty($backgroundOverlayColor) || !empty($backgroundOverlayImage)) {
            $this->addClass('jdb-has-overlay');
            $overlayCSS = new ElementStyle('::after');
            $this->addChildStyle($overlayCSS);

            $backgroundOverlayOpacity = $this->params->get('backgroundOverlayOpacity', null);

            if (\JDPageBuilder\Helper::checkSliderValue($backgroundOverlayOpacity)) {
               $opacity = (int) $backgroundOverlayOpacity->value;
               $overlayCSS->addCss('opacity', ($opacity / 100) . '');
            }
            if (!empty($backgroundOverlayColor)) {
               $overlayCSS->addCss('background-color', $backgroundOverlayColor);
            }
            if (!empty($backgroundOverlayImage)) {
               $overlayCSS->addCss('background-image', 'url(' . \JDPageBuilder\Helper::mediaValue($backgroundOverlayImage) . ')');
               $backgroundOverlayRepeat = $this->params->get('backgroundOverlayRepeat', '');
               if (!empty($backgroundOverlayRepeat)) {
                  $overlayCSS->addCss('background-repeat', $backgroundOverlayRepeat);
               }
               $backgroundOverlaySize = $this->params->get('backgroundOverlaySize', '');
               if (!empty($backgroundOverlaySize)) {
                  if ($backgroundOverlaySize != 'custom') {
                     $overlayCSS->addCss('background-size', $backgroundOverlaySize);
                  } else {
                     $width = $this->params->get('backgroundOverlayWidth', null);
                     if (!empty($width)) {
                        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                           if (isset($width->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
                              $overlayCSS->addCss('background-size', $width->{$deviceKey}->value . $width->{$deviceKey}->unit, $device);
                           }
                        }
                     }
                  }
               }


               $backgroundOverlayAttachment = $this->params->get('backgroundOverlayAttachment', '');
               if (!empty($backgroundOverlayAttachment)) {
                  $overlayCSS->addCss('background-attachment', $backgroundOverlayAttachment);
               }

               $backgroundOverlayPosition = $this->params->get('backgroundOverlayPosition', '');
               if (!empty($backgroundOverlayPosition)) {
                  if ($backgroundOverlayPosition != 'custom') {
                     $overlayCSS->addCss('background-position', $backgroundOverlayPosition);
                  } else {
                     $position = $this->params->get('backgroundOverlayXPosition', null);
                     if (!empty($position)) {
                        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                           if (isset($position->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($position->{$deviceKey})) {
                              $overlayCSS->addCss('background-position-x', $position->{$deviceKey}->value . $position->{$deviceKey}->unit, $device);
                           }
                        }
                     }
                     $position = $this->params->get('backgroundOverlayYPosition', null);
                     if (!empty($position)) {
                        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                           if (isset($position->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($position->{$deviceKey})) {
                              $overlayCSS->addCss('background-position-y', $position->{$deviceKey}->value . $position->{$deviceKey}->unit, $device);
                           }
                        }
                     }
                  }
               }
            }
         }
      }
   }

   public function borderOptions()
   {
      \JDPageBuilder\Helper::applyBorderValue($this->style, $this->params, "border");
   }

   public function spacingOptions()
   {
      $margin = $this->params->get('margin', NULL);
      $padding = $this->params->get('padding', NULL);

      if (!empty($margin)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($margin->{$deviceKey}) && !empty($margin->{$deviceKey})) {

               $css = \JDPageBuilder\Helper::spacingValue($margin->{$deviceKey}, "margin");
               if (!empty($css)) {
                  $this->addStyle($css, $device);
               }
            }
         }
      }

      if (!empty($padding)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {

               $css = \JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding");
               if (!empty($css)) {
                  $this->addStyle($css, $device);
               }
            }
         }
      }

      $zindex = $this->params->get('zIndex', null);
      if (\JDPageBuilder\Helper::checkSliderValue($zindex)) {
         $this->addCss('z-index', $zindex->value);
      }
   }

   public function typographyOptions()
   {
      $headingColor = $this->params->get('typographyHeadingColor', '');
      $textColor = $this->params->get('typographyTextColor', '');
      $linkColor = $this->params->get('typographyLinkColor', '');
      $linkHoverColor = $this->params->get('typographyLinkHoverColor', '');
      $textAlignment = $this->params->get('typographyTextAlignment', null);


      if (!empty($textColor)) {
         $this->addCss('color', $textColor);
      }

      if (!empty($headingColor)) {
         $headinStyle = new ElementStyle('h1,h2,h3,h4,h5,h6');
         $this->addChildStyle($headinStyle);
         $headinStyle->addCss("color", $headingColor);
      }

      if (!empty($linkColor)) {
         $linkStyle = new ElementStyle('a');
         $this->addChildStyle($linkStyle);
         $linkStyle->addCss("color", $linkColor);
      }

      if (!empty($linkHoverColor)) {
         $linkStyle = new ElementStyle('a:hover');
         $this->addChildStyle($linkStyle);
         $linkStyle->addCss("color", $linkHoverColor);
      }

      if (!empty($textAlignment)) {

         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($textAlignment->{$deviceKey}) && !empty($textAlignment->{$deviceKey})) {
               $this->addCss("text-align", $textAlignment->{$deviceKey}, $device);
            }
         }
      }
   }

   public function customCssOptions()
   {
      $customCss = $this->params->get('custom_css', null);
      if (!empty($customCss)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($customCss->{$deviceKey}) && !empty($customCss->{$deviceKey})) {
               \JDPageBuilder\Helper::customCSS($customCss->{$deviceKey}, $this, $device);
            }
         }
      }
   }

   // Advanced Options
   public function initAdvancedOptions()
   {
      // Responsive Options
      $this->responsiveOptions();
      // Animation Options
      $this->animationOptions();
      // ACL Options
      $this->aclOptions();
   }

   public function responsiveOptions()
   {
      $hideDesktop = $this->params->get('hideDesktop', false);
      if ($hideDesktop) {
         $this->addClass('jdb-hide-desktop');
      }
      $hideTablet = $this->params->get('hideTablet', false);
      if ($hideTablet) {
         $this->addClass('jdb-hide-tablet');
      }
      $hideMobile = $this->params->get('hideMobile', false);
      if ($hideMobile) {
         $this->addClass('jdb-hide-mobile');
      }
   }

   public function animationOptions()
   {
      $animation = $this->params->get('animation', '');
      if (!empty($animation)) {

         $options = [];
         $options[] = "type:{$animation}";

         $animationSpeed = $this->params->get('animationSpeed', '');
         if (!empty($animationSpeed)) {
            $options[] = "speed:{$animationSpeed}";
         } else {
            $options[] = "speed:";
         }
         $animationDelay = $this->params->get('animationDelay', '');
         if (!empty($animationDelay)) {
            $options[] = "delay:{$animationDelay}";
         } else {
            $options[] = "delay:";
         }
         $animationInfinite = $this->params->get('animationInfinite', false);
         if ($animationInfinite) {
            $options[] = "infinite:true";
         } else {
            $options[] = "infinite:false";
         }

         $this->addAttribute('jdb-animation', implode(';', $options));
         Builder::loadAnimateCSS();
      } else {
         // $this->addAttribute('jdb-animation', 'type:none');
      }
   }

   public function aclOptions()
   {
      $access = $this->params->get('access', 1);
      $authorised = false;

      $access = is_array($access) ? 1 : $access;
      if (\in_array($access, \JDPageBuilder\Builder::authorised())) {
         $authorised = true;
      } else {
         $authorised = false;
      }
      $this->authorised = $authorised;
   }
}
