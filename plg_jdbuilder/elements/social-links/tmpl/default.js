(function () {

   var JDBuilderElementSocialLinks = function (element) {


      let _html = [];
      var profiles = element.params.get('socialLinks', []);
      if (!profiles.length) {
         return '';
      }

      element.addClass('jdb-social-links');
      var display = element.params.get('slDisplay', 'icon-only');
      element.addClass('jdb-social-links-' + display);
      if (display == "icon-title") {
         element.addClass('jdb-social-links-icon-' + element.params.get('iconPosition', 'left'));
      }

      var animation = element.params.get('slHoverAnimation', '');
      if (animation != '') {
         animation = 'jdb-hover-' + animation;
      }

      var colorStyle = element.params.get('iconStyle', 'brand');
      if (colorStyle == "brand") {
         element.addClass('jdb-brands-icons');
      }

      var invertedColors = element.params.get('brandColorInverted', false);


      _html.push('<ul jdb-tab>');
      let _index = 0;
      profiles.forEach(function (_profile) {
         _index++;
         var linkTargetBlank = (('linkTargetBlank' in _profile) && _profile.linkTargetBlank) ? ' target="_blank"' : '';
         var linkNoFollow = (('linkNoFollow' in _profile) && _profile.linkNoFollow) ? ' rel="nofollow"' : '';


         _html.push('<li class="jdb-social-link-' + _index + ' ' + animation + '">');

         _html.push('<a data-brand="' + _profile.icon.replace(/ /g, "-") + '" title="' + _profile.title + '" class="brand-' + (invertedColors ? 'inverted' : 'static') + '" href="' + _profile.link + '"' + linkTargetBlank + linkNoFollow + '>');
         _html.push('<span class="jdb-sl-icon"><span class="' + _profile.icon + '"></span></span><span class="jdb-sl-title">' + _profile.title + '</span>');
         _html.push('</a>');
         _html.push('</li>');
      });

      _html.push('</ul>');

      socialLinksStyle(element);
      return _html.join('');
   }

   function socialLinksStyle(element) {
      var display = element.params.get('slDisplay', 'icon-only');
      var colorStyle = element.params.get('iconStyle', 'brand');
      var slAlignment = element.params.get('slAlignment', null);
      var invertedColors = element.params.get('brandColorInverted', false);

      if (slAlignment != null) {
         if (typeof slAlignment != 'string') {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in slAlignment) {
                  element.addCss("text-align", slAlignment[_deviceObj.key], _deviceObj.type);
               }
            });
         }
      }

      var linkStyle = JDBRenderer.ElementStyle("> ul li a");
      var linkHoverStyle = JDBRenderer.ElementStyle("> ul li:hover a");


      element.addChildrenStyle([linkStyle, linkHoverStyle]);

      var borderRadius = element.params.get('slBorderRadius', null);
      if (borderRadius !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in borderRadius) {
               linkStyle.addStyle(JDBRenderer.Helper.spacingValue(borderRadius[_deviceObj.key], "radius"), _deviceObj.type);
            }
         });
      }

      var padding = element.params.get('innerPadding', null);
      if (padding !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in padding) {
               linkStyle.addCss("padding", padding[_deviceObj.key].value + 'px', _deviceObj.type);
            }
         });
      }

      let itemSpaceBetween = element.params.get('slSpaceBetween', null);
      if (itemSpaceBetween != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in itemSpaceBetween) && JDBRenderer.Helper.checkSliderValue(itemSpaceBetween[_deviceObj.key])) {
               linkStyle.addCss("margin-right", 'calc(' + itemSpaceBetween[_deviceObj.key].value + 'px / 2)', _deviceObj.type);
               linkStyle.addCss("margin-left", 'calc(' + itemSpaceBetween[_deviceObj.key].value + 'px / 2)', _deviceObj.type);
            }
         });
      }


      let itemBorderStyle = element.params.get('slBorderStyle', 'none');
      linkStyle.addCss("border-style", itemBorderStyle);
      if (itemBorderStyle != 'none') {
         let borderWidth = element.params.get('slBorderWidth', null);
         if (borderWidth !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in borderWidth) {
                  linkStyle.addStyle(JDBRenderer.Helper.spacingValue(borderWidth[_deviceObj.key], "border"), _deviceObj.type);
               }
            });
         }
      }


      linkStyle.addCss("box-shadow", element.params.get('slBoxShadow', ''));
      if (colorStyle != "brand") {
         linkStyle.addCss("color", element.params.get('slColor', ''));
      }
      if (!(colorStyle == "brand" && invertedColors)) {
         linkHoverStyle.addCss("color", element.params.get('slHoverColor', ''));
      }
      if (colorStyle != "brand") {
         linkStyle.addCss("background-color", element.params.get('slBackgroundColor', ''));
      }
      if (!(colorStyle == "brand" && invertedColors)) {
         linkHoverStyle.addCss("background-color", element.params.get('slHoverBackgroundColor', ''));
      }
      if (colorStyle != "brand") {
         linkStyle.addCss("border-color", element.params.get('slBorderColor', ''));
      }
      if (!(colorStyle == "brand" && invertedColors)) {
         linkHoverStyle.addCss("border-color", element.params.get('slBorderHoverColor', ''));
      }
      if (display != 'title-only') {
         var iconStyle = JDBRenderer.ElementStyle("> ul li a .jdb-sl-icon");
         element.addChildStyle(iconStyle);
         var iconSize = element.params.get('iconSize', null);
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in iconSize) && JDBRenderer.Helper.checkSliderValue(iconSize[_deviceObj.key])) {
               iconStyle.addCss("font-size", iconSize[_deviceObj.key].value + 'px', _deviceObj.type);
               iconStyle.addCss("width", iconSize[_deviceObj.key].value + 'px', _deviceObj.type);
               iconStyle.addCss("height", iconSize[_deviceObj.key].value + 'px', _deviceObj.type);
            }
         });
      }

      if (display != 'title-only') {
         var textStyle = JDBRenderer.ElementStyle("> ul li a .jdb-sl-title");
         element.addChildStyle(textStyle);
         var textSize = element.params.get('textSize', null);
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in textSize) && JDBRenderer.Helper.checkSliderValue(textSize[_deviceObj.key])) {
               textStyle.addCss("font-size", textSize[_deviceObj.key].value + 'px', _deviceObj.type);
            }
         });
      }
   }


   window.JDBuilderElementSocialLinks = JDBuilderElementSocialLinks;

})();