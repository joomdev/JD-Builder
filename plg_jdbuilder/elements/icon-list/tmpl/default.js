(function () {

    var JDBuilderElementIconList = function (element) {
        var html = [];
        var items = element.params.get('list_items', []);
        var layout = element.params.get('list_layout', 'list');
        var divider = element.params.get('list_divider', false);

        html = [`<div class="jdb-iconlist jdb-iconlist-layout-${layout}${divider ? ' jdb-iconlist-has-divider' : ''}"><ul class="jdb-iconlist-items">`];

        items.forEach(function (item) {
            if (item.text != '' || item.icon != '') {
                html.push(`<li class="jdb-iconlist-item">${item.icon ? `<span class="jdb-iconlist-icon"><i class="${item.icon}"></i></span>` : ``}<span class="jdb-iconlist-text">${item.text}</span></li>`);
            }
        });

        html.push(`</ul></div>`);

        listStyling(element);
        iconStyling(element);
        textStyling(element);

        return html.join('');
    }

    function listStyling(element) {
        var layout = element.params.get('list_layout', 'list');

        var List = new JDBRenderer.ElementStyle(".jdb-iconlist");
        var ListContainer = new JDBRenderer.ElementStyle(".jdb-iconlist-layout-" + layout);
        var ListItems = new JDBRenderer.ElementStyle(".jdb-iconlist-layout-" + layout + " .jdb-iconlist-items");
        var ListItem = new JDBRenderer.ElementStyle(".jdb-iconlist-layout-" + layout + " .jdb-iconlist-item");
        var ListItemNotLast = new JDBRenderer.ElementStyle(".jdb-iconlist-layout-" + layout + " .jdb-iconlist-item:not(:last-child)");
        var ListItemNotFirst = new JDBRenderer.ElementStyle(".jdb-iconlist-layout-" + layout + " .jdb-iconlist-item:not(:first-child)");
        var ListItemHover = new JDBRenderer.ElementStyle(".jdb-iconlist-item:hover");
        var ListDivider = new JDBRenderer.ElementStyle(".jdb-iconlist-layout-" + layout + ".jdb-iconlist-has-divider .jdb-iconlist-item:not(:last-child):after");

        var alignment = element.params.get('list_alignment', null);

        JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in alignment) {
                ListContainer.addCss('text-align', alignment[_deviceObj.key], _deviceObj.type);
                ListItem.addCss('justify-content', alignment[_deviceObj.key] == 'left' ? 'flex-start' : (alignment[_deviceObj.key] == 'right' ? 'flex-end' : 'center'), _deviceObj.type);
            }
        });


        var space_between = element.params.get('list_space_between', null);
        if (space_between != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if ((_deviceObj.key in space_between) && JDBRenderer.Helper.checkSliderValue(space_between[_deviceObj.key])) {
                    var marginSide = layout == 'list' ? 'bottom' : 'right';
                    ListItemNotLast.addCss(`margin-${marginSide}`, `${space_between[_deviceObj.key].value / 2}px`, _deviceObj.type);
                    ListItemNotLast.addCss(`padding-${marginSide}`, `${space_between[_deviceObj.key].value / 2}px`, _deviceObj.type);
                }
            });
        }

        if (element.params.get('list_divider', false)) {
            var borderSide = layout == 'list' ? 'top' : 'right';
            ListDivider.addCss(`border-${borderSide}-style`, element.params.get('list_divider_style', 'solid'))
            ListDivider.addCss(`border-${borderSide}-color`, element.params.get('list_divider_color', ''))

            var weight = element.params.get('list_divider_weight', null);
            if (weight != null) {
                JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                    if ((_deviceObj.key in weight) && JDBRenderer.Helper.checkSliderValue(weight[_deviceObj.key])) {
                        ListDivider.addCss(`border-${borderSide}-width`, `${weight[_deviceObj.key].value}px`, _deviceObj.type);
                        if (layout == 'list') {
                            ListItemNotLast.addCss(`padding-bottom`, `${weight[_deviceObj.key].value}px`, _deviceObj.type);
                        } else {
                            ListItemNotLast.addCss(`padding-right`, `${weight[_deviceObj.key].value + 5}px`, _deviceObj.type);
                        }
                    }
                });
            }

            if (layout == 'list') {
                var width = element.params.get('list_divider_width', null);
                if (width != null) {
                    JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                        if ((_deviceObj.key in width) && JDBRenderer.Helper.checkSliderValue(width[_deviceObj.key])) {
                            ListDivider.addCss(`width`, `${width[_deviceObj.key].value}${width[_deviceObj.key].unit}`, _deviceObj.type);
                        }
                    });
                }
            } else {
                var height = element.params.get('list_divider_height', null);
                if (height != null) {
                    JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                        if ((_deviceObj.key in height) && JDBRenderer.Helper.checkSliderValue(height[_deviceObj.key])) {
                            ListDivider.addCss(`height`, `${height[_deviceObj.key].value}${height[_deviceObj.key].unit}`, _deviceObj.type);
                        }
                    });
                }
            }

            element.addChildStyle(ListDivider);
        }


        element.addChildStyle(List);
        element.addChildStyle(ListContainer);
        element.addChildStyle(ListItem);
        element.addChildStyle(ListItems);
        element.addChildStyle(ListItemNotLast);
        element.addChildStyle(ListItemNotFirst);
        element.addChildStyle(ListItemHover);
    }

    function iconStyling(element) {
        var Icon = new JDBRenderer.ElementStyle(".jdb-iconlist-icon");
        var IconHover = new JDBRenderer.ElementStyle(".jdb-iconlist-item:hover .jdb-iconlist-icon");

        Icon.addCss('color', element.params.get('icon_color', ''));
        Icon.addCss('background-color', element.params.get('icon_bg_color', ''));
        IconHover.addCss('color', element.params.get('icon_hover_color', ''));
        IconHover.addCss('background-color', element.params.get('icon_bg_hover_color', ''));

        JDBRenderer.Helper.applyBorderValue(Icon, element.params, "iconBorder");

        var icon_size = element.params.get('icon_size', null);
        if (icon_size != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if ((_deviceObj.key in icon_size) && JDBRenderer.Helper.checkSliderValue(icon_size[_deviceObj.key])) {
                    Icon.addCss(`font-size`, `${icon_size[_deviceObj.key].value * 0.70}px`, _deviceObj.type);
                    Icon.addCss(`line-height`, `${icon_size[_deviceObj.key].value}px`, _deviceObj.type);
                    Icon.addCss(`width`, `${icon_size[_deviceObj.key].value}px`, _deviceObj.type);
                    Icon.addCss(`height`, `${icon_size[_deviceObj.key].value}px`, _deviceObj.type);
                }
            });
        }

        var padding = element.params.get('icon_padding', null);
        if (padding != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if (_deviceObj.key in padding) {
                    Icon.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
                }
            });
        }

        var space_between = element.params.get('icon_space_between', null);
        if (space_between != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if ((_deviceObj.key in space_between) && JDBRenderer.Helper.checkSliderValue(space_between[_deviceObj.key])) {
                    Icon.addCss(`margin-right`, `${space_between[_deviceObj.key].value}px`, _deviceObj.type);
                }
            });
        }

        element.addChildStyle(Icon);
        element.addChildStyle(IconHover);
    }

    function textStyling(element) {
        var Text = new JDBRenderer.ElementStyle(".jdb-iconlist-text");
        var TextHover = new JDBRenderer.ElementStyle(".jdb-iconlist-item:hover .jdb-iconlist-text");

        Text.addCss('color', element.params.get('text_color', ''));
        TextHover.addCss('color', element.params.get('text_hover_color', ''));

        var typography = element.params.get('text_typography', null);
        if (typography !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                if (_deviceObj.key in typography) {
                    Text.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
                }
            });
        }

        element.addChildStyle(Text);
        element.addChildStyle(TextHover);
    }

    window.JDBuilderElementIconList = JDBuilderElementIconList;

})();