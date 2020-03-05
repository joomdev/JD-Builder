(function () {

   var JDBuilderElementHeading = function (element) {
      element.addClass('jdb-heading');

      // Params
      var title = element.params.get("title", "");
      var subtitle = element.params.get("subtitle", "");


      // link
      var link = element.params.get("link", "");

      var linkTargetBlank = element.params.get('linkTargetBlank', false);
      var linkTarget = linkTargetBlank ? ' target="_blank"' : "";

      var linkNoFollow = element.params.get('linkNoFollow', false);
      var linkRel = linkNoFollow ? ' rel="nofollow"' : "";

      var alignment = element.params.get('headingAlignment', null);
      if (alignment !== null && typeof alignment == 'object') {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in alignment) {
               element.addCss('text-align', alignment[_deviceObj.key], _deviceObj.type);
            }
         });
      }

      var subtitlePosition = element.params.get('subtitlePosition', 'below');

      var headingHtmlTag = element.params.get("headingHtmlTag", "h3");
      var subheadingHtmlTag = element.params.get("subheadingHtmlTag", "span");

      var subHeadingHTML = subtitle != '' ? '<' + "" + subheadingHtmlTag + "" + ' class="jdb-heading-subheading">' + "" + subtitle + "" + '</' + "" + subheadingHtmlTag + "" + '>' : '';

      headingStyles(element);

      // HTML
      var html = '';
      if (subtitlePosition === "above") {
         html += subHeadingHTML;
      }

      html += '<' + headingHtmlTag + ' class="jdb-heading-heading">';
      if (link !== '') {
         html += '<a title="' + title + '" href="' + link + '"' + linkTarget + '' + linkRel + '>';
      }
      html += title;
      if (link !== '') {
         html += '</a>';
      }
      html += '</' + headingHtmlTag + '>';

      if (subtitlePosition === "below") {
         html += subHeadingHTML;
      }

      return html;
   };

   function headingStyles(element) {
      ['heading', 'subheading'].forEach(function (_heading) {
         var _style = JDBRenderer.ElementStyle("> .jdb-heading-" + _heading);
         element.addChildStyle(_style);

         if (_heading == 'heading') {
            let headingSpacing = element.params.get('headingSpacing', null);
            if (headingSpacing != null) {
               JDBRenderer.DEVICES.forEach(function (_deviceObj) {
                  if ((_deviceObj.key in headingSpacing) && JDBRenderer.Helper.checkSliderValue(headingSpacing[_deviceObj.key])) {
                     var mPos = element.params.get('subtitlePosition', 'below') == 'below' ? 'margin-bottom' : 'margin-top';
                     _style.addCss(mPos, headingSpacing[_deviceObj.key].value + 'px', _deviceObj.type);
                  }
               });
            }
         }

         _style.addCss("color", element.params.get(_heading + "FontColor", ""));
         _style.addCss("text-shadow", element.params.get(_heading + "TextShadow", ""));

         var typography = element.params.get(_heading + "Typography", null);
         if (typography !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in typography) {
                  _style.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
               }
            });
         }
      });
   }

   window.JDBuilderElementHeading = JDBuilderElementHeading;

})();