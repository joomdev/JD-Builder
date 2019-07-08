<?php

namespace JDPageBuilder\Element;

class BaseElement {

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

   public function __construct($object, $parent = null) {
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
         $this->style = new ElementStyle('#' . $this->id);
      }
      // Design Options
      $this->initDesignOptions();

      // Advanced Options
      $this->initAdvancedOptions();
   }

   public function getStart() {
      return '<' . $this->tag . ' id="' . $this->id . '"' . $this->getAttrs() . '>';
   }

   public function getEnd() {
      return '</' . $this->tag . '>';
   }

   public function getContent() {
      return $this->id;
   }

   public function render($output = false) {
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

   public function afterRender() {
      if (empty($this->type)) {
         return;
      }
      foreach ($this->childStyles as $childStyle) {
         $this->addStyle($childStyle->render(true));
      }
      $this->style->render();
   }

   public function getAttrs() {
      $attrs = [];

      if (!empty($this->classes)) {
         $attrs["class"] = implode(" ", $this->classes);
      }

      foreach ($this->attributes as $attribute => $attributeValue) {
         $attrs[$attribute] = $attributeValue;
      }

      $return = [];
      foreach ($attrs as $key => $value) {
         $return[] = $key . '="' . $value . '"';
      }
      $return = implode(" ", $return);
      return (empty($return) ? '' : (' ' . $return));
   }

   public function addClass($class) {
      $this->classes[] = $class;
   }

   public function addAttribute($attribute, $value = '') {
      $this->attributes[$attribute] = $value;
   }

   public function dataAttr($property, $value = "") {
      $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
      $this->attributes['data-' . $property] = $value;
   }

   public function addCss($property, $value, $device = "desktop") {
      if (empty($this->style)) {
         return;
      }
      $this->style->addCss($property, $value, $device);
   }

   public function addStyle($css, $device = "desktop") {
      if (empty($this->style)) {
         return;
      }
      $this->style->addStyle($css, $device);
   }

   public function addChildStyle(ElementStyle $childStyle) {
      $this->childStyles[] = $childStyle;
   }

   public function addChildrenStyle($childStyles = []) {
      foreach ($childStyles as $childStyle) {
         $this->childStyles[] = $childStyle;
      }
   }

   public function addStylesheet($file) {
      \JDPageBuilder\Builder::addStylesheet($file);
   }

   public function addScript($js) {
      \JDPageBuilder\Builder::addScript($js);
   }

   public function addJavascript($file) {
      \JDPageBuilder\Builder::addJavascript($file);
   }

   public function getBackgroundVideo() {
      $background = $this->params->get('background', 'none');
      $return = [];

      if ($background == "video") {
         $videoContent = \JDPageBuilder\Helper::getBGVideoContent($this->params);
      }
      if (!empty($videoContent)) {
         $this->addClass('jdb-has-video-background');
         $return[] = '<div class="jdb-video-background">';
         $return[] = $videoContent;
         $return[] = '</div>';
      }

      return implode("", $return);
   }

   // Basic Options
   public function initDesignOptions() {
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

   public function basicOptions() {
      // custom class
      $custom_class = $this->params->get('custom_class', '');
      if (!empty($custom_class)) {
         $this->addClass($custom_class);
      }

      // custom id
      $custom_id = $this->params->get('custom_id', '');
      if (!empty($custom_id)) {
         $this->id = $custom_id;
      }
   }

   public function backgroundOptions() {
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
                  $this->addCss('background-size', $backgroundSize);
               }
               $backgroundAttachment = $this->params->get('backgroundAttachment', '');
               if (!empty($backgroundAttachment)) {
                  $this->addCss('background-attachment', $backgroundAttachment);
               }
               $backgroundPosition = $this->params->get('backgroundPosition', '');
               if (!empty($backgroundPosition)) {
                  $this->addCss('background-position', $backgroundPosition);
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

   public function backgroundOverlayOptions() {
      $background = $this->params->get('background', 'none');
      if ($background == "image" || $background == "video") {
         $backgroundOverlayColor = $this->params->get('backgroundOverlayColor', '');
         $backgroundOverlayImage = $this->params->get('backgroundOverlayImage', '');
         if (!empty($backgroundOverlayColor) || !empty($backgroundOverlayImage)) {
            $this->addClass('jdb-has-overlay');
            $overlayCSS = [];
            $overlayCSS[] = '&:after{';
            $backgroundOverlayOpacity = $this->params->get('backgroundOverlayOpacity', null);

            if (\JDPageBuilder\Helper::checkSliderValue($backgroundOverlayOpacity)) {

               $overlayCSS[] = 'opacity:' . ($backgroundOverlayOpacity->value / 100) . ';';
            }
            if (!empty($backgroundOverlayColor)) {
               $overlayCSS[] = 'background-color:' . $backgroundOverlayColor . ';';
            }
            if (!empty($backgroundOverlayImage)) {
               $overlayCSS[] = 'background-image:url(' . \JDPageBuilder\Helper::mediaValue($backgroundOverlayImage) . ');';
               $backgroundOverlayRepeat = $this->params->get('backgroundOverlayRepeat', '');
               if (!empty($backgroundOverlayRepeat)) {
                  $overlayCSS[] = 'background-repeat:' . $backgroundOverlayRepeat . ';';
               }
               $backgroundOverlaySize = $this->params->get('backgroundOverlaySize', '');
               if (!empty($backgroundOverlaySize)) {
                  $overlayCSS[] = 'background-size:' . $backgroundOverlaySize . ';';
               }
               $backgroundOverlayAttachment = $this->params->get('backgroundOverlayAttachment', '');
               if (!empty($backgroundOverlayAttachment)) {
                  $overlayCSS[] = 'background-attachment:' . $backgroundOverlayAttachment . ';';
               }
               $backgroundOverlayPosition = $this->params->get('backgroundOverlayPosition', '');
               if (!empty($backgroundOverlayPosition)) {
                  $overlayCSS[] = 'background-position:' . $backgroundOverlayPosition . ';';
               }
            }
            $overlayCSS[] = '}';
            $this->addStyle(implode('', $overlayCSS));
         }
      }
   }

   public function borderOptions() {
      $elementBorderStyle = $this->params->get('borderStyle', '');
      if (!empty($elementBorderStyle)) {
         $this->addCss('border-style', $elementBorderStyle);
         $elementBorderWidth = $this->params->get('borderWidth', null);
         if (!empty($elementBorderWidth) && $elementBorderStyle != 'none') {

            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
               if (isset($elementBorderWidth->{$deviceKey}) && !empty($elementBorderWidth->{$deviceKey})) {

                  $css = \JDPageBuilder\Helper::spacingValue($elementBorderWidth->{$deviceKey}, "border");
                  if (!empty($css)) {
                     $this->addStyle($css, $device);
                  }
               }
            }

            $elementBorderColor = $this->params->get('borderColor', '');
            if (!empty($elementBorderColor)) {
               $this->addCss('border-color', $elementBorderColor);
            }
         }
      }
      $elementBorderRadius = $this->params->get('borderRadius', null);
      if (!empty($elementBorderRadius)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($elementBorderRadius->{$deviceKey}) && !empty($elementBorderRadius->{$deviceKey})) {

               $css = \JDPageBuilder\Helper::spacingValue($elementBorderRadius->{$deviceKey}, "radius");
               if (!empty($css)) {
                  $this->addStyle($css, $device);
               }
            }
         }
      }
      $elementBoxShadow = $this->params->get('boxShadow', '');
      if (!empty($elementBoxShadow)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($elementBoxShadow->{$deviceKey}) && !empty($elementBoxShadow->{$deviceKey})) {
               $this->addCss('box-shadow', $elementBoxShadow->{$deviceKey}, $device);
            }
         }
      }
   }

   public function spacingOptions() {
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

   public function typographyOptions() {
      $headingColor = $this->params->get('typographyHeadingColor', '');
      $textColor = $this->params->get('typographyTextColor', '');
      $linkColor = $this->params->get('typographyLinkColor', '');
      $linkHoverColor = $this->params->get('typographyLinkHoverColor', '');
      $textAlignment = $this->params->get('typographyTextAlignment', null);


      if (!empty($textColor)) {
         $this->addCss('color', $textColor);
      }

      if (!empty($headingColor)) {
         $this->addStyle("h1,h2,h3,h4,h5,h6{color:{$headingColor};}");
      }

      if (!empty($linkColor)) {
         $this->addStyle("a{color:{$linkColor};}");
      }

      if (!empty($linkHoverColor)) {
         $this->addStyle("a:hover{color:{$linkHoverColor};}");
      }

      if (!empty($textAlignment)) {

         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($textAlignment->{$deviceKey}) && !empty($textAlignment->{$deviceKey})) {
               $this->addCss("text-align", $textAlignment->{$deviceKey}, $device);
            }
         }
      }
   }

   public function customCssOptions() {
      $customCss = $this->params->get('custom_css', null);
      if (!empty($customCss)) {
         foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($customCss->{$deviceKey}) && !empty($customCss->{$deviceKey})) {
               $this->addStyle($customCss->{$deviceKey}, $device);
            }
         }
      }
   }

   // Advanced Options
   public function initAdvancedOptions() {
      // Responsive Options
      $this->responsiveOptions();
      // Animation Options
      $this->animationOptions();
      // ACL Options
      $this->aclOptions();
   }

   public function responsiveOptions() {

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

   public function animationOptions() {
      $animation = $this->params->get('animation', '');
      if (!empty($animation)) {

         $options = [];
         $options[] = "type:{$animation}";

         $animationSpeed = $this->params->get('animationSpeed', '');
         if (!empty($animationSpeed)) {
            $options[] = "speed:{$animationSpeed}";
         }
         $animationDelay = $this->params->get('animationDelay', '');
         if (!empty($animationDelay)) {
            $options[] = "delay:{$animationDelay}";
         }
         $animationInfinite = $this->params->get('animationInfinite', false);
         if ($animationInfinite) {
            $options[] = "infinite:true";
         }

         $this->addAttribute('jdb-animation', implode(';', $options));
      }
   }

   public function aclOptions() {
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

class ElementStyle {

   protected $styles = ['desktop' => [], 'mobile' => [], 'tablet' => []];
   protected $css = ['desktop' => [], 'mobile' => [], 'tablet' => []];
   public $selector;

   public function __construct($selector) {
      $this->selector = $selector;
   }

   public function addCss($property, $value, $device = "desktop") {
      if ($value === null || $value === "") {
         return;
      }
      $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
      $this->styles[$device][$property] = $value;
   }

   public function addStyle($css, $device = "desktop") {
      if (empty($css)) {
         return;
      }
      $this->css[$device][] = $css;
   }

   public function render($output = false) {
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
               $cssscript .= $css;
               if (substr($cssscript, -1) != ";") {
                  $cssscript .= ';';
               }
            }
            if (!empty($cssscript)) {
               $scss[$device] .= $cssscript;
            }
         }
      }

      $inlineScss = "";

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
   }

}
