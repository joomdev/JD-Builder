(function () {

   var JDBuilderElementContent = function (element) {
      var content = element.params.get('content', '', 'RAW');
      if (content == '') {
         return;
      }
      element.addClass('jdb-element-content');

      var contentStyle = JDBRenderer.ElementStyle('> .jdb-content *');
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

      let _html = [];
      _html.push('<div class="jdb-content">' + JDBRenderer.Helper.renderHTML(content) + '</div>');
      return _html.join('');
   }

   window.JDBuilderElementContent = JDBuilderElementContent;

})();