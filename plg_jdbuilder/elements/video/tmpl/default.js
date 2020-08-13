(function () {

    var JDBuilderElementVideo = function (element) {
        var videoType = element.params.get('videoType', 'html5');
        switch (videoType) {
            case 'html5':
                var video = JDBRenderer.Helper.mediaValue(element.params.get('video', ''));
                break;
            case 'youtube':
                var video = element.params.get('videoYoutubeLink', '');
                break;
            case 'vimeo':
                var video = element.params.get('videoVimeoLink', '');
                break;
            case 'dailymotion':
                var video = element.params.get('videoDailymotionLink', '');
                break;
        }
        if (video == '') {
            return '';
        }
        var _options = [];
        var uKey = [];
        uKey.push(JDBRenderer.Helper.hashCode(video));

        _options.push('type:' + videoType);
        _options.push('thumbnail:' + (element.params.get('thumbnail', true) ? 'true' : 'false'));
        _options.push('controls:' + (element.params.get('controls', true) ? 'true' : 'false'));
        _options.push('autoplay:' + (element.params.get('autoplay', false) ? 'true' : 'false'));
        _options.push('muted:' + (element.params.get('muted', false) ? 'true' : 'false'));
        _options.push('loop:' + (element.params.get('loop', false) ? 'true' : 'false'));
        _options.push('sticky:' + (element.params.get('sticky', false) ? 'true' : 'false'));
        _options.push('stickyPosition:' + (element.params.get('stickyPosition', 'bottom-right')));
        _options.push('overlay:' + (element.params.get('overlayColor', '')));
        _options.push('size:' + (element.params.get('videoSize', '16by9')));
        _options.push('thumbnailSize:' + element.params.get('ytThumbnailSize', 'maxresdefault'));

        var videoStartTime = element.params.get('videoStartTime', {
            hours: 0,
            minutes: 0
        });
        videoStartTime = (videoStartTime.hours * 60) + videoStartTime.minutes;

        var videoEndTime = element.params.get('videoEndTime', {
            hours: 0,
            minutes: 0
        });
        videoEndTime = (videoEndTime.hours * 60) + videoEndTime.minutes;
        _options.push('ytmb:' + (element.params.get('ytModestBranding', false) ? 'true' : 'false'));
        _options.push('ytsv:' + (element.params.get('ytSuggestedVideos', false) ? 'true' : 'false'));

        _options.push('start:' + videoStartTime);
        _options.push('end:' + videoEndTime);
        _options.push('hideOn:' + element.params.get('stickyHideOn', []).join('-'));
        var poster = '';
        if (element.params.get('poster', '') != '') {
            poster = JDBRenderer.Helper.mediaValue(element.params.get('poster', ''));
        }
        uKey.push(JDBRenderer.Helper.hashCode(poster));
        uKey.push(element.params.get('playButtonType', ''));
        uKey.push(element.params.get('playButtonIcon', ''));
        uKey.push(element.params.get('playButtonImage', ''));

        var _subscriberBar = '';
        if (videoType == 'youtube' && element.params.get('ytSubscribeBar', false)) {
            var _subscriberBarOptions = [];
            _subscriberBarOptions.push(element.params.get('ytSubscribeChennelType', 'channel') + ':' + element.params.get('ytSubscribeChennel', ''));
            _subscriberBarOptions.push('layout:' + (element.params.get('ytSubscribeLayout', 'default')));
            _subscriberBarOptions.push('count:' + (element.params.get('ytSubscribeCount', true) ? 'default' : 'hidden'));
            _subscriberBar = '<div class="jdb-video-subscription-bar jdb-row jdb-no-gutters jdb-justify-content-center"><div class="jdb-col-md-auto jdb-d-flex jdb-align-items-center jdb-justify-content-center jdb-video-subscription-text"><div>' + element.params.get('ytSubscribeText', '') + '</div></div><div class="jdb-col-md-auto jdb-d-flex jdb-align-items-center jdb-justify-content-center jdb-video-subscription-button"><div class="jdb-d-inline-block"><div jdb-ytsubscribe="' + _subscriberBarOptions.join(';') + '"></div></div></div></div>';
        }

        var _vimeoOptions = {};
        if (videoType === 'vimeo') {
            _vimeoOptions.byline = element.params.get('vimeoByline', true);
            _vimeoOptions.title = element.params.get('vimeoTitle', true);
            _vimeoOptions.portrait = element.params.get('vimeoPortrait', true);
            if (element.params.get('vimeoControlColor', '') !== '') {
                _vimeoOptions.color = element.params.get('vimeoControlColor', '').replace('#', '');
            }
            if ((_vimeoOptions.byline || _vimeoOptions.title || _vimeoOptions.portrait) && poster == '') {
                _options.push('thumbnail:false');
            }
            uKey.push(JDBRenderer.Helper.hashCode(JSON.stringify(_vimeoOptions)));
        }

        var _dailymotionOptions = {};
        if (videoType === 'dailymotion') {
            _dailymotionOptions['ui-start-screen-info'] = element.params.get('dailymotionVideoInfo', true);
            _dailymotionOptions['queue-autoplay-next'] = false;
            _dailymotionOptions['ui-logo'] = element.params.get('dailymotionLogo', true);
            if ((_dailymotionOptions['ui-start-screen-info'] || _dailymotionOptions['ui-logo']) && poster == '') {
                _options.push('thumbnail:false');
            }
            uKey.push(JDBRenderer.Helper.hashCode(JSON.stringify(_dailymotionOptions)));
        }

        element.addCss('overflow', 'hidden');
        if (_subscriberBar != '') {
            barStyling(element);
        }
        playButtonStyling(element);
        if (element.params.get('sticky', false)) {
            stickyStyling(element);
        }

        _options.push('animation:' + element.params.get('playButtonAnimation', ''));

        if (element.params.get('thumbnail', true) && element.params.get('lightbox', false) && poster != '') {
            _options.push('lightbox:jdb-video-lightbox-' + element.id);

            var lightboxContent = JDBRenderer.ElementStyle('<#' + 'jdb-video-lightbox-' + element.id + '-content');
            element.addChildrenStyle([lightboxContent]);
            var lightboxContentWidth = element.params.get('lightboxContentWidth', null);
            if (JDBRenderer.Helper.checkSliderValue(lightboxContentWidth)) {
                lightboxContent.addCss('width', lightboxContentWidth.value + 'vw');
            }
        }else{
            _options.push('lightbox:');
        }

        _options.push('ukey:' + JDBRenderer.Helper.hashCode(uKey.join('')));
        return '<div class="jdb-video-container"><div jdb-video="' + _options.join(';') + '" data-src="' + video + '" data-play="' + element.params.get('playButtonType', '') + '" data-play-icon="' + element.params.get('playButtonIcon', '') + '" data-play-image="' + JDBRenderer.Helper.mediaValue(element.params.get('playButtonImage', '')) + '" data-thumbnail="' + poster + '" data-vimeo=\'' + JSON.stringify(_vimeoOptions) + '\' data-dailymotion=\'' + JSON.stringify(_dailymotionOptions) + '\'></div>' + _subscriberBar + '</div>';
    };

    function barStyling(element) {
        var barStyle = JDBRenderer.ElementStyle('.jdb-video-subscription-bar');
        var buttonStyle = JDBRenderer.ElementStyle('.jdb-ytsubscribe');

        element.addChildrenStyle([barStyle, buttonStyle]);

        barStyle.addCss("color", element.params.get('ytSubscribeColor', ''));
        barStyle.addCss("background-color", element.params.get('ytSubscribeBackground', ''));

        var typography = element.params.get('ytSubscribeTypography', null);
        if (typography !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if (_deviceObj.key in typography) {
                    barStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
                }
            });
        }

        var padding = element.params.get('ytSubscribePadding', null);
        if (padding !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if (_deviceObj.key in padding) {
                    barStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
                }
            });
        }

        let spacing = element.params.get('ytSubscribeSpacing', null);
        if (spacing != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if ((_deviceObj.key in spacing) && JDBRenderer.Helper.checkSliderValue(spacing[_deviceObj.key])) {
                    buttonStyle.addCss("margin-left", spacing[_deviceObj.key].value + 'px', _deviceObj.type);
                }
            });
        }
    }

    function playButtonStyling(element) {
        if (element.params.get('playButtonType', '') === 'none') {
            return;
        }
        if (!element.params.get('thumbnail', true) || (element.params.get('thumbnail', true) && element.params.get('poster', '') == '')) {
            return;
        }
        var playStyle = JDBRenderer.ElementStyle('.jdb-video-playicon');
        var playHoverStyle = JDBRenderer.ElementStyle('.jdb-video-play:hover .jdb-video-playicon');
        element.addChildrenStyle([playStyle, playHoverStyle]);

        if (element.params.get('playButtonType', '') != 'image') {
            playStyle.addCss("color", element.params.get('playButtonColor', ''));
            playHoverStyle.addCss("color", element.params.get('playButtonHoverColor', ''));
            playStyle.addCss("background-color", element.params.get('playButtonBackground', ''));
            playHoverStyle.addCss("background-color", element.params.get('playButtonHoverBackground', ''));
        }


        let spacing = element.params.get('playButtonSize', null);
        if (spacing != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if ((_deviceObj.key in spacing) && JDBRenderer.Helper.checkSliderValue(spacing[_deviceObj.key])) {
                    playStyle.addCss("width", spacing[_deviceObj.key].value + 'px', _deviceObj.type);
                    playStyle.addCss("height", spacing[_deviceObj.key].value + 'px', _deviceObj.type);
                    playStyle.addCss("font-size", parseInt(spacing[_deviceObj.key].value * .6, 10) + 'px', _deviceObj.type);
                    if (element.params.get('playButtonType', '') == 'image') {
                        playStyle.addCss("padding", '0px', _deviceObj.type);
                    } else {
                        playStyle.addCss("padding", parseInt(spacing[_deviceObj.key].value / 5, 10) + 'px', _deviceObj.type);
                    }
                }
            });
        }


        JDBRenderer.Helper.applyBorderValue(playStyle, element.params, "playButtonBorder");
        JDBRenderer.Helper.applyBorderValue(playHoverStyle, element.params, "playButtonHoverBorder");
    }

    function stickyStyling(element) {
        var stickyStyle = JDBRenderer.ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-wrapper');
        var wrapperStyle = JDBRenderer.ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-play');
        var thumbnailStyle = JDBRenderer.ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-play .jdb-video-thumbnail');
        var playerStyle = JDBRenderer.ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-player');
        var closeStyle = JDBRenderer.ElementStyle('.jdb-video.jdb-video-sticky .jdb-video-sticky-close');
        element.addChildrenStyle([stickyStyle, thumbnailStyle, playerStyle, closeStyle, wrapperStyle]);

        var width = element.params.get('stickyWidth', null);
        if (width != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if ((_deviceObj.key in width) && JDBRenderer.Helper.checkSliderValue(width[_deviceObj.key])) {
                    if (width[_deviceObj.key].value != '' && width[_deviceObj.key].value != null) {
                        stickyStyle.addCss('width', width[_deviceObj.key].value + width[_deviceObj.key].unit, _deviceObj.type);
                    }
                }
            });
        }

        var margin = element.params.get('stickySpacing', null);
        if (margin != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if (_deviceObj.key in margin) {
                    stickyStyle.addStyle(JDBRenderer.Helper.spacingValue(margin[_deviceObj.key], "margin"), _deviceObj.type);
                }
            });
        }


        var padding = element.params.get('stickyPadding', null);
        if (padding !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if (_deviceObj.key in padding) {
                    wrapperStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
                    thumbnailStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
                    playerStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
                }
            });
        }


        closeStyle.addCss('color', element.params.get('stickyCloseColor', ''));
        thumbnailStyle.addCss('background-color', element.params.get('stickyBackground', ''));
        playerStyle.addCss('background-color', element.params.get('stickyBackground', ''));
    }

    window.JDBuilderElementVideo = JDBuilderElementVideo;

})();