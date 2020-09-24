<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

$items = $element->params->get('list_items', []);
$layout = $element->params->get('list_layout', 'list');
$divider = $element->params->get('list_divider', false);

$html = ["<div class=\"jdb-iconlist jdb-iconlist-layout-{$layout}" . ($divider ? ' jdb-iconlist-has-divider' : '') . "\"><ul class=\"jdb-iconlist-items\">"];

foreach ($items as $item) {
    if (@$item->text != '' || @$item->icon != '') {
        if (!empty($item->icon)) {
            \JDPageBuilder\Builder::loadFontLibraryByIcon($item->icon);
        }
        $html[] = "<li class=\"jdb-iconlist-item\">" . ($item->icon ? "<span class=\"jdb-iconlist-icon\"><i class=\"{$item->icon}\"></i></span>" : "") . "<span class=\"jdb-iconlist-text\">{$item->text}</span></li>";
    }
}

$html[] = "</ul></div>";
echo implode("", $html);

/* List Layout */


$List = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist");
$ListContainer = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-layout-" . $layout);
$ListItems = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-layout-{$layout} .jdb-iconlist-items");
$ListItem = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-layout-" . $layout . " .jdb-iconlist-item");
$ListItemNotLast = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-layout-{$layout} .jdb-iconlist-item:not(:last-child)");
$ListItemNotFirst = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-layout-{$layout} .jdb-iconlist-item:not(:first-child)");
$ListItemHover = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-item:hover");
$ListDivider = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-layout-" . $layout . ".jdb-iconlist-has-divider .jdb-iconlist-item:not(:last-child):after");


$alignment = $element->params->get('list_alignment', null);
if ($alignment != null) {
    foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($alignment->{$deviceKey})) {
            $ListContainer->addCss('text-align', $alignment->{$deviceKey}, $device);
            $ListItem->addCss('justify-content', $alignment->{$deviceKey} == 'left' ? 'flex-start' : ($alignment->{$deviceKey} == 'right' ? 'flex-end' : 'center'), $device);
        }
    }
}

$space_between = $element->params->get('list_space_between', null);
if ($space_between != null) {
    foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($space_between->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($space_between->{$deviceKey})) {
            $marginSide = $layout == 'list' ? 'bottom' : 'right';
            $ListItemNotLast->addCss("margin-{$marginSide}", ($space_between->{$deviceKey}->value / 2) . "px", $device);
            $ListItemNotLast->addCss("padding-{$marginSide}", ($space_between->{$deviceKey}->value / 2) . "px", $device);
        }
    }
}

if ($element->params->get('list_divider', false)) {
    $borderSide = $layout == 'list' ? 'top' : 'right';

    $ListDivider->addCss("border-{$borderSide}-style", $element->params->get('list_divider_style', 'solid'));
    $ListDivider->addCss("border-{$borderSide}-color", $element->params->get('list_divider_color', ''));

    $weight = $element->params->get('list_divider_weight', null);
    if ($weight != null) {
        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($weight->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($weight->{$deviceKey})) {
                $ListDivider->addCss("border-{$borderSide}-width", ($weight->{$deviceKey}->value) . "px", $device);
                if ($layout == 'list') {
                    $ListItemNotLast->addCss("padding-bottom", ($weight->{$deviceKey}->value) . "px", $device);
                } else {
                    $ListItemNotLast->addCss("padding-right", ($weight->{$deviceKey}->value + 5) . "px", $device);
                }
            }
        }
    }

    if ($layout == 'list') {
        $width = $element->params->get('list_divider_width', null);
        if ($width != null) {
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                if (isset($width->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
                    $ListDivider->addCss("width", ($width->{$deviceKey}->value) . $width->{$deviceKey}->unit, $device);
                }
            }
        }
    } else {
        $height = $element->params->get('list_divider_height', null);
        if ($height != null) {
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                if (isset($height->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($height->{$deviceKey})) {
                    $ListDivider->addCss("height", ($height->{$deviceKey}->value) . "px", $device);
                }
            }
        }
    }
    $element->addChildStyle($ListDivider);
}


$element->addChildStyle($List);
$element->addChildStyle($ListContainer);
$element->addChildStyle($ListItem);
$element->addChildStyle($ListItems);
$element->addChildStyle($ListItemNotLast);
$element->addChildStyle($ListItemNotFirst);
$element->addChildStyle($ListItemHover);


$Icon = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-icon");
$IconHover = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-item:hover .jdb-iconlist-icon");

$Icon->addCss('color', $element->params->get('icon_color', ''));
$Icon->addCss('background-color', $element->params->get('icon_bg_color', ''));
$IconHover->addCss('color', $element->params->get('icon_hover_color', ''));
$IconHover->addCss('background-color', $element->params->get('icon_bg_hover_color', ''));

JDPageBuilder\Helper::applyBorderValue($Icon, $element->params, "iconBorder");

$icon_size = $element->params->get('icon_size', null);
if ($icon_size != null) {

    foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($icon_size->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($icon_size->{$deviceKey})) {
            $Icon->addCss("font-size", ($icon_size->{$deviceKey}->value * 0.70) . "px", $device);
            $Icon->addCss("line-height", ($icon_size->{$deviceKey}->value) . "px", $device);
            $Icon->addCss("width", ($icon_size->{$deviceKey}->value) . "px", $device);
            $Icon->addCss("height", ($icon_size->{$deviceKey}->value) . "px", $device);
        }
    }
}

$padding = $element->params->get('icon_padding', null);
if (!empty($padding)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
            $Icon->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
        }
    }
}

$space_between = $element->params->get('icon_space_between', null);
if ($space_between != null) {
    foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($space_between->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($space_between->{$deviceKey})) {
            $Icon->addCss("margin-right", $space_between->{$deviceKey}->value . "px", $device);
        }
    }
}

$element->addChildStyle($Icon);
$element->addChildStyle($IconHover);


$Text = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-text");
$TextHover = new JDPageBuilder\Element\ElementStyle(".jdb-iconlist-item:hover .jdb-iconlist-text");

$Text->addCss('color', $element->params->get('text_color', ''));
$TextHover->addCss('color', $element->params->get('text_hover_color', ''));

$typography = $element->params->get('text_typography', null);
if (!empty($typography)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
            $Text->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
        }
    }
}

$element->addChildStyle($Text);
$element->addChildStyle($TextHover);
