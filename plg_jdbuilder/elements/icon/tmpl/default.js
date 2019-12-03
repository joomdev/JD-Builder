(function () {

   var JDBuilderElementIcon = function (element) {

      var icon = element.params.get('icon', '');
      if (icon == '') {
         return '';
      }

      JDBRenderer.Document.loadFontLibraryByIcon(icon);
      element.addClass('jdb-icon');


      // link
      var link = element.params.get("link", "");

      var linkTargetBlank = element.params.get('linkTargetBlank', false);
      var linkTarget = linkTargetBlank ? ' target="_blank"' : "";

      var linkNoFollow = element.params.get('linkNoFollow', false);
      var linkRel = linkNoFollow ? ' rel="nofollow"' : "";


      var animation = element.params.get('iconHoverAnimation', '');
      animation = animation == '' ? '' : ' jdb-hover-' + animation;

      iconStyling(element);
      var _html = [];

      if (link != '') {
         _html.push('<a class="jdb-icon-wrapper' + animation + '" href="' + link + '"' + linkTarget + linkRel + '>');
      } else {
         _html.push('<div class="jdb-icon-wrapper' + animation + '">');
      }

      _html.push('<span class="' + icon + '"></span>');

      if (link != '') {
         _html.push('</a>');
      } else {
         _html.push('</div>');
      }
      return _html.join('');
   };

   function iconStyling(element) {
      var alignment = element.params.get('iconAlignment', null);
      if (alignment !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in alignment) {
               element.addCss('text-align', alignment[_deviceObj.key], _deviceObj.type);
            }
         });
      }


      var iconStyle = JDBRenderer.ElementStyle('> .jdb-icon-wrapper');
      var iconInnerStyle = JDBRenderer.ElementStyle('> .jdb-icon-wrapper > span');
      var iconHoverStyle = JDBRenderer.ElementStyle('> .jdb-icon-wrapper:hover');

      element.addChildrenStyle([iconStyle, iconHoverStyle, iconInnerStyle]);

      switch (element.params.get('iconShape', 'circle')) {
         case 'rounded':
            var borderRadius = element.params.get('iconBorderRadius', null);
            if (borderRadius !== null) {
               JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                  if (_deviceObj.key in borderRadius) {
                     iconStyle.addStyle(JDBRenderer.Helper.spacingValue(borderRadius[_deviceObj.key], "radius"), _deviceObj.type);
                  }
               });
            }
            break;
         case 'circle':
            iconStyle.addCss("border-radius", "50%");
            break;
         case 'square':
            iconStyle.addCss("border-radius", "0");
            break;
      }

      // Icon Size
      var iconSize = element.params.get('iconSize', null);
      if (iconSize != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in iconSize) && JDBRenderer.Helper.checkSliderValue(iconSize[_deviceObj.key])) {
               iconStyle.addCss("font-size", (iconSize[_deviceObj.key].value * 0.70) + 'px', _deviceObj.type);
               iconStyle.addCss("width", iconSize[_deviceObj.key].value + 'px', _deviceObj.type);
               iconStyle.addCss("height", iconSize[_deviceObj.key].value + 'px', _deviceObj.type);
               iconStyle.addCss("line-height", iconSize[_deviceObj.key].value + 'px', _deviceObj.type);
            }
         });
      }

      // Icon Rotate
      var iconRotate = element.params.get('iconRotate', null);
      if (iconRotate != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in iconRotate) && JDBRenderer.Helper.checkSliderValue(iconRotate[_deviceObj.key])) {
               iconInnerStyle.addCss("transform", "rotate(" + iconRotate[_deviceObj.key].value + "deg)", _deviceObj.type);
            }
         });
      }

      // Icon Padding
      var padding = element.params.get('iconPadding', null);
      if (padding !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in padding) {
               iconStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
            }
         });
      }



      iconStyle.addCss("color", element.params.get('iconTextColor', ''));
      iconStyle.addCss("background-color", element.params.get('iconBackgroundColor', ''));
      iconStyle.addCss("border-color", element.params.get('iconBorderColor', ''));
      iconStyle.addCss("background-image", element.params.get('iconGradient', ''));
      iconStyle.addCss("box-shadow", element.params.get('iconBoxShadow', ''));


      iconHoverStyle.addCss("color", element.params.get('iconTextColorHover', ''));
      iconHoverStyle.addCss("background-color", element.params.get('iconBackgroundColorHover', ''));
      iconHoverStyle.addCss("border-color", element.params.get('iconBorderColorHover', ''));
      iconHoverStyle.addCss("background-image", element.params.get('iconGradientHover', ''));

      // border
      let iconBorderStyle = element.params.get('iconBorderStyle', 'none');
      iconStyle.addCss("border-style", iconBorderStyle);

      if (iconBorderStyle != 'none') {
         let borderWidth = element.params.get('iconBorderWidth', null);
         if (borderWidth !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in borderWidth) {
                  iconStyle.addStyle(JDBRenderer.Helper.spacingValue(borderWidth[_deviceObj.key], "border"), _deviceObj.type);
               }
            });
         }
      }
   }

   window.JDBuilderElementIcon = JDBuilderElementIcon;

})();