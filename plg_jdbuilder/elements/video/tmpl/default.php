<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);

$element->addCss('overflow', 'hidden');

$videoType = $element->params->get('videoType', 'html5');
switch ($videoType) {
    case 'html5':
        $video = JDPageBuilder\Helper::mediaValue($element->params->get('video', ''));
        break;
    case 'youtube':
        $video =  $element->params->get('videoYoutubeLink', '');
        break;
    case 'vimeo':
        $video =  $element->params->get('videoVimeoLink', '');
        break;
    case 'dailymotion':
        $video =  $element->params->get('videoDailymotionLink', '');
        break;
}
if (empty($video)) {
    return;
}

\JDPageBuilder\Builder::addJavascript(\JURI::root() . 'media/jdbuilder/js/jdvideo.min.js');


$options = [];

$options[] = 'type:' . $videoType;
$options[] = 'thumbnail:' . ($element->params->get('thumbnail', true) ? 'true' : 'false');
$options[] = 'controls:' . ($element->params->get('controls', true) ? 'true' : 'false');
$options[] = 'autoplay:' . ($element->params->get('autoplay', false) ? 'true' : 'false');
$options[] = 'muted:' . ($element->params->get('muted', false) ? 'true' : 'false');
$options[] = 'loop:' . ($element->params->get('loop', false) ? 'true' : 'false');
$options[] = 'sticky:' . ($element->params->get('sticky', false) ? 'true' : 'false');
$options[] = 'stickyPosition:' . ($element->params->get('stickyPosition', 'bottom-right'));
$options[] = 'overlay:' . ($element->params->get('overlayColor', ''));
$options[] = 'size:' . ($element->params->get('videoSize', '16by9'));
$options[] = 'thumbnailSize:' . $element->params->get('ytThumbnailSize', 'maxresdefault');
$options[] = 'ytmb:' . ($element->params->get('ytModestBranding', false) ? 'true' : 'false');
$options[] = 'ytsv:' . ($element->params->get('ytSuggestedVideos', false) ? 'true' : 'false');

$videoStartTime =  $element->params->get('videoStartTime', \json_decode('{"hours":0,"minutes":0}'));
$videoStartTime = isset($videoStartTime->hours) ? (($videoStartTime->hours * 60) + $videoStartTime->minutes) : 0;

$videoEndTime =  $element->params->get('videoEndTime', \json_decode('{"hours":0,"minutes":0}'));
$videoEndTime = isset($videoEndTime->hours) ? (($videoEndTime->hours * 60) + $videoEndTime->minutes) : 0;

$options[] = 'start:' . $videoStartTime;
$options[] = 'end:' . $videoEndTime;
$options[] = 'animation:' . $element->params->get('playButtonAnimation', '');


$poster = '';
if ($element->params->get('poster', '') != '') {
    $poster = JDPageBuilder\Helper::mediaValue($element->params->get('poster', ''));
}

$subscriberBar = '';
if ($videoType == 'youtube' &&  $element->params->get('ytSubscribeBar', false)) {
    \JDPageBuilder\Builder::addJavascript(\JURI::root() . 'media/jdbuilder/js/jdytsubscriber.min.js');
    $subscriberBarOptions = [];
    $subscriberBarOptions[] = $element->params->get('ytSubscribeChennelType', 'channel') . ':' .  $element->params->get('ytSubscribeChennel', '');
    $subscriberBarOptions[] = 'layout:' . ($element->params->get('ytSubscribeLayout', 'default'));
    $subscriberBarOptions[] = 'count:' . ($element->params->get('ytSubscribeCount', true) ? 'default' : 'hidden');
    $subscriberBar = '<div class="jdb-video-subscription-bar jdb-row jdb-no-gutters jdb-justify-content-center"><div class="jdb-col-md-auto jdb-d-flex jdb-align-items-center jdb-justify-content-center jdb-video-subscription-text"><div>' .  $element->params->get('ytSubscribeText', '') . '</div></div><div class="jdb-col-md-auto jdb-d-flex jdb-align-items-center jdb-justify-content-center jdb-video-subscription-button"><div class="jdb-d-inline-block"><div jdb-ytsubscribe="' . implode(';', $subscriberBarOptions) . '"></div></div></div></div>';
}


$element->addCss('overflow', 'hidden');

$vimeoOptions = [];
if ($videoType == 'vimeo') {
    $vimeoOptions['byline'] = $element->params->get('vimeoByline', true);
    $vimeoOptions['title'] = $element->params->get('vimeoTitle', true);
    $vimeoOptions['portrait'] = $element->params->get('vimeoPortrait', true);
    if ($element->params->get('vimeoControlColor', '') !== '') {
        $vimeoOptions['color'] = str_replace('#', '', $element->params->get('vimeoControlColor', ''));
    }
    if (($vimeoOptions['byline'] || $vimeoOptions['title'] || $vimeoOptions['portrait']) && $poster == '') {
        $options[] = 'thumbnail:false';
    }
}

$dailymotionOptions = [];
if ($videoType == 'dailymotion') {
    $dailymotionOptions['ui-start-screen-info'] = $element->params->get('dailymotionVideoInfo', true);
    $dailymotionOptions['queue-autoplay-next'] = false;
    $dailymotionOptions['ui-logo'] = $element->params->get('dailymotionLogo', true);
    if (($dailymotionOptions['ui-start-screen-info'] || $dailymotionOptions['ui-logo']) && $poster == '') {
        $options[] = 'thumbnail:false';
    }
}

if ($element->params->get('thumbnail', false) && $element->params->get('lightbox', false) && !empty($poster)) {
    $options[] = 'lightbox:jdb-video-lightbox-' . $element->id;
    JDPageBuilder\Builder::loadLightBox();

    $lightboxContent = new JDPageBuilder\Element\ElementStyle('<#jdb-video-lightbox-' . $element->id . '-content');

    $element->addChildrenStyle([$lightboxContent]);

    $lightboxContentWidth = $element->params->get('lightboxContentWidth', null);
    if (\JDPageBuilder\Helper::checkSliderValue($lightboxContentWidth)) {
        $lightboxContent->addCss('width', $lightboxContentWidth->value . 'vw');
    }
}

?>

<div class="jdb-video-container">
    <div jdb-video="<?php echo implode(';', $options); ?>" data-src="<?php echo $video; ?>" data-play="<?php echo  $element->params->get('playButtonType', ''); ?>" data-play-icon="<?php echo  $element->params->get('playButtonIcon', ''); ?>" data-play-image="<?php echo JDPageBuilder\Helper::mediaValue($element->params->get('playButtonImage', '')); ?>" data-thumbnail="<?php echo $poster; ?>" data-vimeo='<?php echo json_encode($vimeoOptions); ?>' data-dailymotion='<?php echo json_encode($dailymotionOptions); ?>'></div>
    <?php echo $subscriberBar; ?>
</div>

<!-- Styling Element -->
<?php
// Bar Styling
if (!empty($subscriberBar)) {
    $barStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video-subscription-bar');
    $buttonStyle = new JDPageBuilder\Element\ElementStyle('.jdb-ytsubscribe');

    $element->addChildrenStyle([$barStyle, $buttonStyle]);

    $barStyle->addCss("color", $element->params->get('ytSubscribeColor', ''));
    $barStyle->addCss("background-color", $element->params->get('ytSubscribeBackground', ''));

    $typography = $element->params->get('ytSubscribeTypography', null);
    if (!empty($typography)) {
        foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
                $barStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
            }
        }
    }

    $padding = $element->params->get('ytSubscribePadding', null);
    if (!empty($padding)) {
        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
                $barStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
            }
        }
    }

    $spacing = $element->params->get('ytSubscribeSpacing', null);
    if (!empty($spacing)) {
        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($spacing->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($spacing->{$deviceKey})) {
                $buttonStyle->addCss("margin-left", $spacing->{$deviceKey}->value . 'px', $device);
            }
        }
    }
}

// Play Button Styling
if ($element->params->get('playButtonType', '') != 'none') {
    if ($element->params->get('thumbnail', true) && $element->params->get('poster', '') != '') {

        if ($element->params->get('playButtonType', '') == 'icon') {
            \JDPageBuilder\Builder::loadFontLibraryByIcon($element->params->get('playButtonIcon', ''));
        }
        $playStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video-playicon');
        $playHoverStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video-play:hover .jdb-video-playicon');
        $element->addChildrenStyle([$playStyle, $playHoverStyle]);

        if ($element->params->get('playButtonType', '') != 'image') {
            $playStyle->addCss("color", $element->params->get('playButtonColor', ''));
            $playHoverStyle->addCss("color", $element->params->get('playButtonHoverColor', ''));
            $playStyle->addCss("background-color", $element->params->get('playButtonBackground', ''));
            $playHoverStyle->addCss("background-color", $element->params->get('playButtonHoverBackground', ''));
        }



        $spacing = $element->params->get('playButtonSize', null);
        if (!empty($spacing)) {
            foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
                if (isset($spacing->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($spacing->{$deviceKey})) {
                    $playStyle->addCss("width", $spacing->{$deviceKey}->value . 'px', $device);
                    $playStyle->addCss("height", $spacing->{$deviceKey}->value . 'px', $device);
                    $playStyle->addCss("font-size", ((int) ($spacing->{$deviceKey}->value * .6)) . 'px', $device);
                    if ($element->params->get('playButtonType', '') == 'image') {
                        $playStyle->addCss("padding",  '0px', $device);
                    } else {
                        $playStyle->addCss("padding", ((int) ($spacing->{$deviceKey}->value / 5)) . 'px', $device);
                    }
                }
            }
        }


        JDPageBuilder\Helper::applyBorderValue($playStyle, $element->params, "playButtonBorder");
        JDPageBuilder\Helper::applyBorderValue($playHoverStyle, $element->params, "playButtonHoverBorder");
    }
}

if ($element->params->get('sticky', false)) {
    $stickyStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-wrapper');
    $wrapperStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-play');
    $thumbnailStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-play .jdb-video-thumbnail');
    $playerStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-player');
    $closeStyle = new JDPageBuilder\Element\ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-sticky-close');


    $element->addChildrenStyle([$stickyStyle, $thumbnailStyle, $playerStyle, $closeStyle, $wrapperStyle]);

    $width = $element->params->get('stickyWidth', null);
    if (!empty($width)) {
        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($width->{$deviceKey}) && \JDPageBuilder\Helper::checkSliderValue($width->{$deviceKey})) {
                if ($width->{$deviceKey}->value != '' && $width->{$deviceKey}->value != null) {
                    $stickyStyle->addCss('width', $width->{$deviceKey}->value . $width->{$deviceKey}->unit, $device);
                }
            }
        }
    }

    $margin = $element->params->get('stickySpacing', null);
    if (!empty($margin)) {
        foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($margin->{$deviceKey}) && !empty($margin->{$deviceKey})) {
                $stickyStyle->addStyle(JDPageBuilder\Helper::spacingValue($margin->{$deviceKey}, "margin"), $device);
            }
        }
    }

    $padding = $element->params->get('stickyPadding', null);
    if (!empty($padding)) {
        foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($padding->{$deviceKey}) && !empty($padding->{$deviceKey})) {
                $wrapperStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
                $thumbnailStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
                $playerStyle->addStyle(JDPageBuilder\Helper::spacingValue($padding->{$deviceKey}, "padding"), $device);
            }
        }
    }

    $closeStyle->addCss('color', $element->params->get('stickyCloseColor', ''));
    $thumbnailStyle->addCss('background-color', $element->params->get('stickyBackground', ''));
    $playerStyle->addCss('background-color', $element->params->get('stickyBackground', ''));
}

if (\JDPageBuilder\Builder::getDocument()->lightBox) {
?>
    <script>
        refreshJDLightbox();
    </script>
<?php
}
?>