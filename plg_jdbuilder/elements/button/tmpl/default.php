<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

// button title
$defaultButtonTitle = $element->params->exists('buttonText') ? '' : 'Button';
$buttonTitle = $element->params->get('buttonText', $defaultButtonTitle);

$buttonClass = [];
$buttonClass[] = 'jdb-button';

$buttonWrapperStyle = new JDPageBuilder\Element\ElementStyle('.jdb-button-wrapper');
$buttonStyle = new JDPageBuilder\Element\ElementStyle('.jdb-button-link');
$buttonHoverStyle = new JDPageBuilder\Element\ElementStyle('.jdb-button-link:hover');

$element->addChildrenStyle([$buttonStyle, $buttonHoverStyle, $buttonWrapperStyle]);

$class = ['jdb-button-link'];

// button type
$buttonType = $element->params->get('buttonType', 'primary');
$buttonClass[] = 'jdb-button-' . $buttonType;

// button size
$buttonSize = $element->params->get('buttonSize', '');
if (!empty($buttonSize)) {
   $buttonClass[] = 'jdb-button-' . $buttonSize;
}

// button alignment
$alignment = $element->params->get('buttonAlignment', null);
if (!empty($alignment)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($alignment->{$deviceKey}) && !empty($alignment->{$deviceKey})) {
         $align = $alignment->{$deviceKey};
         if ($align != 'block') {
            $element->addCss('text-align', $align, $device);
         } else {
            $buttonStyle->addCss("width", "100%", $device);
         }
      }
   }
}


$alignment = $element->params->get('buttonAlignment', null);
if (!empty($alignment)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($alignment->{$deviceKey}) && !empty($alignment->{$deviceKey})) {
         $align = $alignment->{$deviceKey};
         if ($align != 'block') {
            $buttonWrapperStyle->addCss('flex', '0 0 auto', $device);
            $buttonWrapperStyle->addCss('-ms-flex', '0 0 auto', $device);
            $buttonWrapperStyle->addCss('width', 'auto', $device);
            if ($align == 'center') {
               $buttonWrapperStyle->addCss('margin-right', 'auto', $device);
               $buttonWrapperStyle->addCss('margin-left', 'auto', $device);
            } else if ($align == 'right') {
               $buttonWrapperStyle->addCss('margin-right', 'initial', $device);
               $buttonWrapperStyle->addCss('margin-left', 'auto', $device);
            } else {
               $buttonWrapperStyle->addCss('margin-right', 'auto', $device);
               $buttonWrapperStyle->addCss('margin-left', 'initial', $device);
            }
         } else {
            $buttonWrapperStyle->addCss('flex', '0 0 100%', $device);
            $buttonWrapperStyle->addCss('-ms-flex', '0 0 100%', $device);
            $buttonWrapperStyle->addCss('width', '100%', $device);
            $buttonWrapperStyle->addCss('margin-right', 'initial', $device);
            $buttonWrapperStyle->addCss('margin-left', 'initial', $device);
         }
      }
   }
}


// button link
$link = $element->params->get('link', '#');
$link = empty($link) ? '#' : $link;

// link target
$linkTargetBlank = $element->params->get('linkTargetBlank', FALSE);
$linkTarget = $linkTargetBlank ? ' target="_blank"' : "";

// link follow
$linkNoFollow = $element->params->get('linkNoFollow', FALSE);
$linkRel = $linkNoFollow ? ' rel="nofollow"' : "";

// Animation
$animation = $element->params->get('buttonAnimation', '');
if (!empty($animation)) {
   $class[] = 'jdb-hover-' . $animation;
}

// button icon
$iconHTML = '';
$buttonIcon = $element->params->get('buttonIcon', '');
$iconPosition = $element->params->get('iconPosition', 'right');
if (!empty($buttonIcon)) {
   $iconStyle = new JDPageBuilder\Element\ElementStyle('.jdb-button-link > .jdb-button-icon');

   $element->addChildStyle($iconStyle);
   $iconAnimation = $element->params->get('iconAnimation', '');
   if (!empty($iconAnimation)) {
      $class[] = 'jdb-hover-' . $iconAnimation;
   }

   \JDPageBuilder\Builder::loadFontLibraryByIcon($buttonIcon);
   $iconHTML = '<span class="jdb-button-icon jdb-hover-icon ' . $buttonIcon . ' jdb-button-icon-' . $iconPosition . '"></span>';

   $iconSpacing = $element->params->get('iconSpacing', null);
   if (JDPageBuilder\Helper::checkSliderValue($iconSpacing)) {
      if ($iconPosition == "right") {
         $iconStyle->addCss("margin-left", $iconSpacing->value . "px");
      } else {
         $iconStyle->addCss("margin-right", $iconSpacing->value . "px");
      }
   }
}

// Background
$buttonStyle->addCss("background-color", $element->params->get('buttonBackgroundColor', ''));
$buttonHoverStyle->addCss("background-color", $element->params->get('buttonBackgroundColorHover', ''));

// Text Color
$buttonStyle->addCss("color", $element->params->get('buttonTextColor', ''));
$buttonHoverStyle->addCss("color", $element->params->get('buttonTextColorHover', ''));


// Border Color
$buttonStyle->addCss("border-color", $element->params->get('buttonBorderColor', ''));
$buttonHoverStyle->addCss("border-color", $element->params->get('buttonBorderColorHover', ''));

// Gradient
$buttonStyle->addCss("background-image", $element->params->get('buttonGradient', ''));
$buttonHoverStyle->addCss("background-image", $element->params->get('buttonGradientHover', ''));
if (!empty($element->params->get('buttonGradient', '')) && empty($element->params->get('buttonGradientHover', ''))) {
   $buttonHoverStyle->addCss("background-image", 'none');
}

// Typography
$typography = $element->params->get('buttonTypography', null);
if (!empty($typography)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
         $buttonStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
      }
   }
}

// Padding
$padding = $element->params->get('buttonPadding', null);
if (!empty($padding)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
         $buttonStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
      }
   }
}

// Border
$borderType = $element->params->get('buttonBorderStyle', 'solid');
$buttonStyle->addCss("border-style", $borderType);
if ($borderType != 'none') {
   $borderWidth = $element->params->get('buttonBorderWidth', null);
   if ($borderWidth != null) {
      foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
         if (isset($borderWidth->{$deviceKey}) && !empty($borderWidth->{$deviceKey})) {
            $css = JDPageBuilder\Helper::spacingValue($borderWidth->{$deviceKey}, "border");
            $buttonStyle->addStyle($css, $device);
         }
      }
   }
}

// Radius
$borderRadius = $element->params->get('buttonBorderRadius', null);
if (!empty($borderRadius)) {
   foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
      if (isset($borderRadius->{$deviceKey}) && !empty($borderRadius->{$deviceKey})) {
         $css = JDPageBuilder\Helper::spacingValue($borderRadius->{$deviceKey}, "radius");
         $buttonStyle->addStyle($css, $device);
      }
   }
}

// shadow
$buttonStyle->addCss("box-shadow", $element->params->get('buttonBoxShadow', ''));
?>
<div class="jdb-button-container">
   <div class="jdb-button-wrapper">
      <div class="<?php echo implode(' ', $buttonClass); ?>">
         <a class="<?php echo implode(" ", $class); ?>" href="<?php echo $link; ?>" title="<?php echo $buttonTitle; ?>" <?php echo $linkTarget; ?><?php echo $linkRel; ?>>
            <?php
            if ($iconPosition == 'left') {
               echo $iconHTML;
            }
            echo $buttonTitle;
            if ($iconPosition == 'right') {
               echo $iconHTML;
            }
            ?>
         </a>
      </div>
   </div>
</div>