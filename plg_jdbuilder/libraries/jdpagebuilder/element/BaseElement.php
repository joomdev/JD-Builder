<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

use JDPageBuilder\Builder;
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
   public $livepreview = false;
   public $childStyles = [];

   public function __construct($object, $parent = null)
   {
      $request = \JDPageBuilder\Builder::request();
      if ($request->get('jdb-preview', 0) || $request->get('task', '') == 'livePreview') {
         $this->livepreview = true;
      }
      $this->id = isset($object->id) ? $object->id : null;
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
            if (substr($childselector, 0, 7) === '::hover' || substr($childselector, 0, 7) === '::active' || substr($childselector, 0, 7) === '::focus') {
               $childselector = substr($childselector, 1);
            }
            $selector[] = $this->style->selector . $glue . $childselector;
         }
         $childStyle->selector = implode(',', $selector);
      } else {
         $glue = " ";
         if (substr($childStyle->selector, 0, 2) === '::') {
            $glue = "";
         }

         if (substr($childStyle->selector, 0, 7) === '::hover' || substr($childStyle->selector, 0, 7) === '::active' || substr($childStyle->selector, 0, 7) === '::focus') {
            $childStyle->selector = substr($childStyle->selector, 1);
         }

         $childStyle->selector = $this->style->selector . $glue . $childStyle->selector;
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
      $return = [];

      if ($background == "video") {
         $this->addClass('jdb-has-video-background');
         $return[] = '<div class="jdb-video-background" jdb-video="url:' . \JDPageBuilder\Helper::mediaValue($this->params->get('backgroundVideoMedia', '')) . ';autoplay:true;muted:true;loop:true;">';
         $return[] = '</div>';
      }

      return implode("", $return);
   }

   public function getParticlesBackground()
   {
      $background = $this->params->get('enableParticlesBackground', false);
      $return = [];

      if ($background) {
         $this->addClass('jdb-has-particle-bg');

         // options
         $count = $this->params->get('particlesBackgroundCount', \json_decode('{value:80}', false));
         $color = $this->params->get('particlesBackgroundColor', '#ffffff');
         $shape = $this->params->get('particlesBackgroundShape', 'circle');
         $size = $this->params->get('particlesBackgroundSize', \json_decode('{value:10}', false));

         $options = [];
         $options[] = 'count:' . $count->value;
         if ($color != '') {
            $options[] = 'color:' . $color;
         }
         $options[] = 'shape:' . $shape;
         $options[] = 'size:' . $size->value;

         // end options

         $return[] = '<div jdb-particles data-id="' . $this->id . '" data-options="' . implode(';', $options) . '"></div>';

         Builder::loadParticleJS();
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

      $request = \JDPageBuilder\Builder::request();

      if ($request->get('task', '') == 'livePreview') {
         return;
      }

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
         $this->addAttribute('jdb-animation', 'type:none');
      }
   }

   public function aclOptions()
   {
      $access = $this->params->get('access', []);
      $authorised = false;
      $auh = \JDPageBuilder\Builder::authorised();
      if (!empty($access)) {
         foreach ($access as $acl) {
            if (\in_array($acl, \JDPageBuilder\Builder::authorised())) {
               $authorised = true;
            }
         }
      } else {
         $authorised = true;
      }
      $this->authorised = $authorised;
   }
}

class ElementStyle
{

   protected $styles = ['desktop' => [], 'mobile' => [], 'tablet' => []];
   protected $css = ['desktop' => [], 'mobile' => [], 'tablet' => []];
   public $selector;

   public function __construct($selector)
   {
      $this->selector = $selector;
   }

   public function addCss($property, $value, $device = "desktop")
   {
      if ($value === null || $value === "") {
         return;
      }
      if (is_string($value)) {
         $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
         $this->styles[$device][$property] = $value;
      }
   }

   public function addStyle($css, $device = "desktop")
   {
      if (empty($css)) {
         return;
      }
      $this->css[$device][] = $css;
   }

   public function render($output = false)
   {
      $scss = [];

      foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         $scss[$device] = "";
      }

      foreach ($this->styles as $device => $styles) {
         if (!empty($styles)) {
            foreach ($styles as $property => $value) {
               $scss[$device] .= "{$property}:{$value};";
            }
         }
      }

      foreach ($this->css as $device => $cssScripts) {
         if (!empty($cssScripts)) {
            $cssscript = "";
            foreach ($cssScripts as $css) {
               if (is_string($css)) {
                  $cssscript .= $css;
                  if (substr($cssscript, -1) != ";") {
                     $cssscript .= ';';
                  }
               }
            }
            if (!empty($cssscript)) {
               $scss[$device] .= $cssscript;
            }
         }
      }

      $inlineScss = [];
      foreach ($scss as $device => $script) {
         if ($script != '') {
            $inlineScss[$device] = $this->selector . ' {' . $script . '}';
         }
      }

      \JDPageBuilder\Builder::addStyle($inlineScss);

      /*
      foreach ($scss as $device => $cssscript) {
         if (!empty($cssscript)) {
            if ($device != "desktop") {
               if ($device == "tablet") {
                  $inlineScss .= '@media (min-width: 768px) and (max-width: 991.98px) {';
               } else {
                  $inlineScss .= '@media (max-width: 767.98px) {';
               }
            }
            $inlineScss .= $cssscript;
            if ($device != "desktop") {
               $inlineScss .= '}';
            }
         }
      }

      if (empty($inlineScss)) {
         return '';
      }

      $scss = $this->selector . " {" . $inlineScss . "}";
      if (!$output) {
         \JDPageBuilder\Builder::addStyle($scss);
      } else {
         return $scss;
      }
      */
   }
}
