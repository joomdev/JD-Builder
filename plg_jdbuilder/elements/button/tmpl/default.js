(function () {

   var JDBuilderElementButton = function (element) {

      // button title
      var buttonTitle = element.params.get('buttonText', '');
      var buttonClass = [];
      buttonClass.push('jdb-button');

      let buttonWrapperStyle = JDBRenderer.ElementStyle('.jdb-button-wrapper');
      let buttonStyle = JDBRenderer.ElementStyle('.jdb-button-link');
      let buttonHoverStyle = JDBRenderer.ElementStyle('.jdb-button-link:hover');

      element.addChildStyle(buttonStyle);
      element.addChildStyle(buttonHoverStyle);
      element.addChildStyle(buttonWrapperStyle);

      var _class = [];
      _class.push('jdb-button-link');

      // button type
      var buttonType = element.params.get('buttonType', 'primary');
      buttonClass.push('jdb-button-' + buttonType);

      // button size
      var buttonSize = element.params.get('buttonSize', '');
      if (buttonSize != '') {
         buttonClass.push('jdb-button-' + buttonSize);
      }

      // Button Alignment
      var alignment = element.params.get('buttonAlignment', null);
      if (alignment != null) {

         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in alignment) {
               var align = alignment[_deviceObj.key];
               if (align != 'block') {
                  buttonWrapperStyle.addCss('flex', '0 0 auto', _deviceObj.type);
                  buttonWrapperStyle.addCss('-ms-flex', '0 0 auto', _deviceObj.type);
                  buttonWrapperStyle.addCss('width', 'auto', _deviceObj.type);
                  if (align == 'center') {
                     buttonWrapperStyle.addCss('margin-right', 'auto', _deviceObj.type);
                     buttonWrapperStyle.addCss('margin-left', 'auto', _deviceObj.type);
                  } else if (align == 'right') {
                     buttonWrapperStyle.addCss('margin-right', 'initial', _deviceObj.type);
                     buttonWrapperStyle.addCss('margin-left', 'auto', _deviceObj.type);
                  } else {
                     buttonWrapperStyle.addCss('margin-right', 'auto', _deviceObj.type);
                     buttonWrapperStyle.addCss('margin-left', 'initial', _deviceObj.type);
                  }
               } else {
                  buttonWrapperStyle.addCss('flex', '0 0 100%', _deviceObj.type);
                  buttonWrapperStyle.addCss('-ms-flex', '0 0 100%', _deviceObj.type);
                  buttonWrapperStyle.addCss('width', '100%', _deviceObj.type);
                  buttonWrapperStyle.addCss('margin-right', 'initial', _deviceObj.type);
                  buttonWrapperStyle.addCss('margin-left', 'initial', _deviceObj.type);
               }
            }
         });
      }




      // link
      var link = element.params.get("link", "#");

      var linkTargetBlank = element.params.get('linkTargetBlank', false);
      var linkTarget = linkTargetBlank ? ' target="_blank"' : "";

      var linkNoFollow = element.params.get('linkNoFollow', false);
      var linkRel = linkNoFollow ? ' rel="nofollow"' : "";

      // Animation
      var animation = element.params.get('buttonAnimation', '');
      if (animation != '') {
         _class.push('jdb-hover-' + animation);
      }

      // button icon
      var iconHTML = '';
      var buttonIcon = element.params.get('buttonIcon', '');
      var iconPosition = element.params.get('iconPosition', 'right');
      if (buttonIcon != '') {
         var iconStyle = JDBRenderer.ElementStyle('.jdb-button-link > .jdb-button-icon');
         element.addChildStyle(iconStyle);

         var iconAnimation = element.params.get('iconAnimation', '');
         if (iconAnimation != '') {
            _class.push('jdb-hover-' + iconAnimation);
         }

         JDBRenderer.Document.loadFontLibraryByIcon(buttonIcon);
         iconHTML = '<span class="jdb-button-icon jdb-hover-icon ' + buttonIcon + ' jdb-button-icon-' + iconPosition + '"></span>';

         var iconSpacing = element.params.get('iconSpacing', null);
         if (JDBRenderer.Helper.checkSliderValue(iconSpacing)) {
            if (iconPosition == "right") {
               iconStyle.addCss("margin-left", iconSpacing.value + "" + "px");
            } else {
               iconStyle.addCss("margin-right", iconSpacing.value + "" + "px");
            }
         }
      }

      // Background
      buttonStyle.addCss("background-color", element.params.get('buttonBackgroundColor', ''));
      buttonHoverStyle.addCss("background-color", element.params.get('buttonBackgroundColorHover', ''));

      // Text Color
      buttonStyle.addCss("color", element.params.get('buttonTextColor', ''));
      buttonHoverStyle.addCss("color", element.params.get('buttonTextColorHover', ''));


      // Border Color
      buttonStyle.addCss("border-color", element.params.get('buttonBorderColor', ''));
      buttonHoverStyle.addCss("border-color", element.params.get('buttonBorderColorHover', ''));

      // Gradient
      buttonStyle.addCss("background-image", element.params.get('buttonGradient', ''));
      buttonHoverStyle.addCss("background-image", element.params.get('buttonGradientHover', ''));

      if (element.params.get('buttonGradient', '') != '' && element.params.get('buttonGradientHover', '') == '') {
         buttonHoverStyle.addCss("background-image", 'none');
      }

      // typography
      var typography = element.params.get("buttonTypography", null);

      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               buttonStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      // Padding
      var padding = element.params.get('buttonPadding', null);
      if (padding != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in padding) {
               buttonStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
            }
         });
      }

      // Border
      let iconBorderStyle = element.params.get('buttonBorderStyle', 'solid');
      buttonStyle.addCss("border-style", iconBorderStyle);

      if (iconBorderStyle != 'none') {
         let borderWidth = element.params.get('buttonBorderWidth', null);
         if (borderWidth !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in borderWidth) {
                  buttonStyle.addStyle(JDBRenderer.Helper.spacingValue(borderWidth[_deviceObj.key], "border"), _deviceObj.type);
               }
            });
         }
      }

      // Radius
      var borderRadius = element.params.get('buttonBorderRadius', null);
      if (borderRadius !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in borderRadius) {
               buttonStyle.addStyle(JDBRenderer.Helper.spacingValue(borderRadius[_deviceObj.key], "radius"), _deviceObj.type);
            }
         });
      }

      // Box Shadow
      buttonStyle.addCss("box-shadow", element.params.get('buttonBoxShadow', ''));

      var _html = [];
      _html.push('<div class="jdb-button-container">')
      _html.push('<div class="jdb-button-wrapper">')
      _html.push('<div class="' + buttonClass.join(' ') + '">')
      _html.push('<a class="' + _class.join(" ") + '" href="' + link + '" title="' + buttonTitle + '"' + linkTarget + '' + linkRel + '>');

      if (iconPosition == 'left') {
         _html.push(iconHTML);
      }

      _html.push(buttonTitle);

      if (iconPosition == 'right') {
         _html.push(iconHTML);
      }

      _html.push('</a>');
      _html.push('</div>');
      _html.push('</div>');
      _html.push('</div>');

      return _html.join("");
   }

   window.JDBuilderElementButton = JDBuilderElementButton;

})();