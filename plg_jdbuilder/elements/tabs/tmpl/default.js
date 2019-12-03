(function () {

   var JDBuilderElementTabs = function (element) {
      let _html = [];
      var items = element.params.get('items', []);
      if (!items.length) {
         return '';
      }

      element.addClass('jdb-tabs');
      var tabType = element.params.get('tabType', 'horizontal');
      element.addClass('jdb-tabs-' + tabType);
      if (tabType == 'horizontal') {
         element.addClass('jdb-tabs-align-left jdb-tabs-top');
      } else {
         element.addClass('jdb-tabs-left');
      }


      _html.push('<ul jdb-tab>');

      items.forEach(function (_item) {
         _html.push('<li>');
         _html.push('<a class="jdb-tab-title jdb-icon-' + element.params.get('tabsIconPosition', 'left') + '" href="#">');
         if (('icon' in _item) && _item.icon != '') {
            _html.push('<i class="jdb-tab-icon ' + _item.icon + '"></i>');
         }
         _html.push('<span>' + _item.title + '</span>');
         _html.push('</a>');
         _html.push('</li>');
      });

      _html.push('</ul>');
      _html.push('<ul class="jdb-tab-contents">');
      items.forEach(function (_item) {
         _html.push('<li class="jdb-tab-content">');
         _html.push(_item.content);
         _html.push('</li>');
      });
      _html.push('</ul>');

      tabStyle(element);
      return _html.join('');
   }

   function tabStyle(element) {
      var tabsStyle = JDBRenderer.ElementStyle('> .jdb-tab');
      var tabsBorderStyle = JDBRenderer.ElementStyle('> .jdb-tab:after');
      var tabStyleLi = JDBRenderer.ElementStyle('> .jdb-tab > li');
      var tabStyle = JDBRenderer.ElementStyle('> .jdb-tab > li a');
      var tabStyleHover = JDBRenderer.ElementStyle('> .jdb-tab > li:hover a');
      var tabStyleActive = JDBRenderer.ElementStyle('> .jdb-tab > li.jdb-active a');
      var contentStyle = JDBRenderer.ElementStyle('> .jdb-tab-contents > li.jdb-tab-content');
      var iconStyle = JDBRenderer.ElementStyle('> .jdb-tab > li > a > .jdb-tab-icon');

      element.addChildrenStyle([tabsStyle, tabsBorderStyle, tabStyleLi, tabStyle, tabStyleHover, tabStyleActive, contentStyle, iconStyle]);

      // tabs styling
      var tabType = element.params.get('tabType', 'horizontal');

      if (tabType == 'vertical') {
         var tabsVerticalWidth = element.params.get('tabsVerticalWidth', null);
         if (tabsVerticalWidth != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if ((_deviceObj.key in tabsVerticalWidth) && JDBRenderer.Helper.checkSliderValue(tabsVerticalWidth[_deviceObj.key])) {
                  tabsStyle.addCss("min-width", tabsVerticalWidth[_deviceObj.key].value + tabsVerticalWidth[_deviceObj.key].unit, _deviceObj.type);
                  tabsStyle.addCss("max-width", tabsVerticalWidth[_deviceObj.key].value + tabsVerticalWidth[_deviceObj.key].unit, _deviceObj.type);
                  tabsStyle.addCss("width", tabsVerticalWidth[_deviceObj.key].value + tabsVerticalWidth[_deviceObj.key].unit, _deviceObj.type);
               }
            });
         }
      }

      tabStyle.addCss("color", element.params.get('tabsColor', ''));
      tabStyleHover.addCss("color", element.params.get('tabsColorHover', ''));
      tabStyleActive.addCss("color", element.params.get('tabsColorActive', ''));

      var typography = element.params.get('tabsTypography', null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               tabStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      var padding = element.params.get('tabsPadding', null);
      if (padding !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in padding) {
               tabStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
            }
         });
      }

      // content

      contentStyle.addCss("color", element.params.get('contentColor', ''));
      contentStyle.addCss("background-color", element.params.get('contentBackground', ''));

      var typography = element.params.get('tabContentTypography', null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               contentStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      var padding = element.params.get('tabContentPadding', null);
      if (padding !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in padding) {
               contentStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
            }
         });
      }

      var contentBorderStyle = element.params.get('contentBorderStyle', 'none');
      contentStyle.addCss("border-style", contentBorderStyle);

      if (contentBorderStyle != 'none') {
         let borderWidth = element.params.get('contentBorderWidth', null);
         if (borderWidth !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in borderWidth) {
                  contentStyle.addStyle(JDBRenderer.Helper.spacingValue(borderWidth[_deviceObj.key], "border"), _deviceObj.type);
               }
            });
         }
         contentStyle.addCss("border-color", element.params.get('contentBorderColor', ''));
      }

      var tabsIconSize = element.params.get('tabsIconSize', null);
      if (tabsIconSize !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in tabsIconSize) && JDBRenderer.Helper.checkSliderValue(tabsIconSize[_deviceObj.key])) {
               iconStyle.addCss("font-size", tabsIconSize[_deviceObj.key].value + 'px', _deviceObj.type);
            }
         });
      }
   }


   window.JDBuilderElementTabs = JDBuilderElementTabs;

})();