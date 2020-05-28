(function () {

   var JDBuilderElementContent = function (element) {
      var content = element.params.get('content', '', 'RAW');
      var dropcap = element.params.get('dropcap', false);
      if (content == '') {
         return;
      }
      element.addClass('jdb-element-content');

      var contentStyle = JDBRenderer.ElementStyle('> .jdb-content');
      element.addChildStyle(contentStyle);

      // typography
      var typography = element.params.get("contentTypography", null);

      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               contentStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      var _content = content;

      let _html = [];
      let _dropcap = '';
      if (dropcap) {
         var _firstWord = JDBRenderer.Helper.firstWord(content);
         var _firstLetter = JDBRenderer.Helper.firstLetter(_firstWord);

         _content = _content.replace(_firstWord, _firstWord.substring(1));
         _dropcap = '<span class="jdb-firstletter">' + _firstLetter + '</span>';

         var dropcapStyle = JDBRenderer.ElementStyle('.jdb-firstletter');
         element.addChildStyle(dropcapStyle);

         var typography = element.params.get("dropcapTypography", null);
         if (typography !== null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if (_deviceObj.key in typography) {
                  dropcapStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
               }
            });
         }

         dropcapStyle.addCss('color', element.params.get("dropcapColor", ''));
         dropcapStyle.addCss('background-color', element.params.get("dropcapBackground", ''));

      }

      _html.push('<div class="jdb-content">' + _dropcap + _content + '</div>');

      return _html.join('');
   }

   window.JDBuilderElementContent = JDBuilderElementContent;

})();