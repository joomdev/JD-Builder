(function () {

   var JDBuilderElementAccordion = function (element) {

      var items = element.params.get('items', []);
      if (!items.length) {
         return;
      }

      element.addClass('jdb-accordion-element');

      var collapsible = element.params.get('accordionCollapsible', true);
      var multiple = element.params.get('accordionMultiple', false);
      if (collapsible) {
         var firstactive = element.params.get('accordionFirstActive', true);
      } else {
         firstactive = false;
      }
      if (multiple) {
         var opanAll = element.params.get('accordionOpenAll', false);
      } else {
         opanAll = false;
      }

      var titleTag = element.params.get('titleTag', '');
      titleTag = (titleTag == '') ? 'span' : titleTag;

      var _html = [];

      var faqSchema = element.params.get('faqSchema', false);

      _html.push('<ul ' + (faqSchema ? 'itemScope itemType="https://schema.org/FAQPage" ' : '') + 'jdb-accordion="collapsible:' + (collapsible ? 'true' : 'false') + ';active:' + (firstactive ? 0 : 'false') + ';multiple:' + (multiple ? 'true' : 'false') + '">');

      items.forEach(function (_item) {
         let _li = [];
         _li.push('<li ' + (faqSchema ? 'itemScope itemProp="mainEntity" itemType="https://schema.org/Question" ' : '') + '' + (opanAll ? ' class="jdb-active"' : '') + '>');
         _li.push('<a class="jdb-accordion-title jdb-caret-' + (element.params.get('accordionIconAlignment', 'right')) + '" href="#">');
         _li.push('<' + titleTag + ' ' + (faqSchema ? 'itemProp="name" ' : '') + 'class="jdb-accordion-text">');
         if (typeof _item.icon != 'undefined' && _item.icon != '') {
            JDBRenderer.Document.loadFontLibraryByIcon(_item.icon);
            _li.push('<i class="jdb-accordion-icon ' + _item.icon + '"></i>');
         }
         _li.push(_item.title);
         _li.push('</' + titleTag + '>');
         _li.push(JDBRenderer.Helper.caretValue(element.params.get('accordionIcon', '')));
         _li.push('</a>');
         _li.push('<div ' + (faqSchema ? 'itemScope itemProp="acceptedAnswer" itemType="https://schema.org/Answer" ' : '') + 'class="jdb-accordion-content"><div' + (faqSchema ? ' itemProp="text"' : '') + '>' + _item.content + '</div></div>')
         _li.push('</li>');
         _html.push(_li.join(''));
      });

      _html.push('</ul>');



      accordionStyle(element);

      return _html.join('');
   }

   function accordionStyle(element) {
      var itemStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li');
      var itemHoverStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li:not(.jdb-active):hover');
      var itemActiveStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li.jdb-active');

      var titleStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li > .jdb-accordion-title');
      var titleTextStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li > .jdb-accordion-title .jdb-accordion-text');
      var titleHoverStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li:not(.jdb-active):hover > .jdb-accordion-title');
      var titleActiveStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li.jdb-active > .jdb-accordion-title');
      var titleActiveTextStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li.jdb-active > .jdb-accordion-title .jdb-accordion-text');


      var contentStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li > div > div');

      var caretStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li > a > .jdb-caret');
      var caretActiveStyle = JDBRenderer.ElementStyle('> .jdb-accordion > li.jdb-active > a > .jdb-caret');

      element.addChildrenStyle([itemStyle, itemHoverStyle, itemActiveStyle, titleStyle, titleTextStyle, titleHoverStyle, titleActiveStyle, contentStyle, caretStyle, caretActiveStyle, titleActiveTextStyle]);


      let itemSpaceBetween = element.params.get('itemSpaceBetween', null);
      if (itemSpaceBetween != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in itemSpaceBetween) && JDBRenderer.Helper.checkSliderValue(itemSpaceBetween[_deviceObj.key])) {
               itemStyle.addCss("margin-bottom", itemSpaceBetween[_deviceObj.key].value + 'px', _deviceObj.type);
            }
         });
      }

      let itemBorderStyle = element.params.get('itemBorderStyle', 'none');
      itemStyle.addCss("border-style", itemBorderStyle);
      if (itemBorderStyle != 'none') {
         let borderWidth = element.params.get('itemBorderWidth', null);
         if (borderWidth !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in borderWidth) {
                  itemStyle.addStyle(JDBRenderer.Helper.spacingValue(borderWidth[_deviceObj.key], "border"), _deviceObj.type);
               }
            });
         } else {
            itemStyle.addCss("border-width", '1px');
         }

         itemStyle.addCss("border-color", element.params.get('itemBorderColor', ''));
         itemHoverStyle.addCss("border-color", element.params.get('itemBorderColorHover', ''));
         itemActiveStyle.addCss("border-color", element.params.get('itemBorderColorActive', ''));
      }


      var borderRadius = element.params.get('itemBorderRadius', null);
      if (borderRadius !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in borderRadius) {
               let _radiusStyle = JDBRenderer.Helper.spacingValue(borderRadius[_deviceObj.key], "radius");

               itemStyle.addStyle(_radiusStyle, _deviceObj.type);
               titleStyle.addStyle(_radiusStyle, _deviceObj.type);
               contentStyle.addStyle(_radiusStyle, _deviceObj.type);

               titleStyle.addCss("border-bottom-right-radius", 0, _deviceObj.type);
               titleStyle.addCss("border-bottom-left-radius", 0, _deviceObj.type);
               contentStyle.addCss("border-top-right-radius", 0, _deviceObj.type);
               contentStyle.addCss("border-top-left-radius", 0, _deviceObj.type);

            }
         });
      }

      var boxShadow = element.params.get('itemBoxShadow', null);
      itemStyle.addCss('box-shadow', boxShadow);

      titleTextStyle.addCss("color", element.params.get('titleColor', ''));

      var typography = element.params.get('accordionTitleTypography', null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               titleTextStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);

               if ('size' in typography[_deviceObj.key]) {
                  caretStyle.addCss("min-width", "calc(" + typography[_deviceObj.key].size + typography[_deviceObj.key].sizeUnit + " + 10px)", _deviceObj.type);
                  caretStyle.addCss("max-width", "calc(" + typography[_deviceObj.key].size + typography[_deviceObj.key].sizeUnit + " + 10px)", _deviceObj.type);
                  caretStyle.addCss("min-height", "calc(" + typography[_deviceObj.key].size + typography[_deviceObj.key].sizeUnit + " + 10px)", _deviceObj.type);
                  caretStyle.addCss("max-height", "calc(" + typography[_deviceObj.key].size + typography[_deviceObj.key].sizeUnit + " + 10px)", _deviceObj.type);
                  caretStyle.addCss("line-height", "calc(" + typography[_deviceObj.key].size + typography[_deviceObj.key].sizeUnit + " + 10px)", _deviceObj.type);
                  caretStyle.addCss("font-size", typography[_deviceObj.key].size + typography[_deviceObj.key].sizeUnit, _deviceObj.type);
               }
            }
         });
      }

      var padding = element.params.get('accordionTitlePadding', null);
      if (padding !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in padding) {
               titleStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
            }
         });
      }


      let titleBorderStyle = element.params.get('titleBorderStyle', 'none');
      titleStyle.addCss("border-style", titleBorderStyle);
      if (titleBorderStyle != 'none') {
         let borderWidth = element.params.get('titleBorderWidth', null);
         if (borderWidth !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in borderWidth) {
                  titleStyle.addStyle(JDBRenderer.Helper.spacingValue(borderWidth[_deviceObj.key], "border"), _deviceObj.type);
               }
            });
         }
         titleStyle.addCss("border-color", element.params.get('titleBorderColor', ''));
         titleHoverStyle.addCss("border-color", element.params.get('titleBorderColorHover', ''));
         titleActiveStyle.addCss("border-color", element.params.get('titleBorderColorActive', ''));
      }

      titleStyle.addCss("background-color", element.params.get('titleBackground', ''));
      titleActiveTextStyle.addCss("color", element.params.get('titleColorActive', ''));
      titleActiveStyle.addCss("background-color", element.params.get('titleBackgroundActive', ''));

      contentStyle.addCss("color", element.params.get('contentColor', ''));
      contentStyle.addCss("background-color", element.params.get('contentBackground', ''));
      contentStyle.addCss("background-color", element.params.get('contentBackground', ''));

      var typography = element.params.get('accordionContentTypography', null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               contentStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      var padding = element.params.get('accordionContentPadding', null);
      if (padding !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in padding) {
               contentStyle.addStyle(JDBRenderer.Helper.spacingValue(padding[_deviceObj.key], "padding"), _deviceObj.type);
            }
         });
      } else {
         contentStyle.addCss("padding", "10px");
      }

      var contentBorderStyle = element.params.get('contentBorderStyle', 'solid');
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

      caretStyle.addCss("color", element.params.get('caretColor', ''));
      caretStyle.addCss("background-color", element.params.get('caretBackgroundColor', ''));
      caretActiveStyle.addCss("color", element.params.get('caretColorActive', ''));
      caretActiveStyle.addCss("background-color", element.params.get('caretBackgroundColorActive', ''));
   }

   window.JDBuilderElementAccordion = JDBuilderElementAccordion;

})();