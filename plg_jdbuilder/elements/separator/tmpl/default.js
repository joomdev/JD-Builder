(function () {

   var JDBuilderElementSeparator = function (element) {
      element.addClass('jdb-divider');
      var style = JDBRenderer.ElementStyle('> hr');
      element.addChildStyle(style);

      style.addCss("border-style", element.params.get('separatorType', 'solid'));
      style.addCss("border-color", element.params.get('separatorColor', ''));

      var weight = element.params.get('separatorWeight', null);
      if (weight != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in weight) && JDBRenderer.Helper.checkSliderValue(weight[_deviceObj.key])) {
               style.addCss("border-top-width", weight[_deviceObj.key].value + weight[_deviceObj.key].unit, _deviceObj.type);
            }
         });
      }

      var width = element.params.get('separatorWidth', null);
      if (width != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in width) && JDBRenderer.Helper.checkSliderValue(width[_deviceObj.key])) {
               style.addCss("width", width[_deviceObj.key].value + width[_deviceObj.key].unit, _deviceObj.type);
            }
         });
      }

      var gap = element.params.get('separatorGap', null);
      if (gap != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in gap) && JDBRenderer.Helper.checkSliderValue(gap[_deviceObj.key])) {
               style.addCss("margin-bottom", gap[_deviceObj.key].value + "px", _deviceObj.type);
               style.addCss("margin-top", gap[_deviceObj.key].value + "px", _deviceObj.type);
            }
         });
      }


      var alignment = element.params.get('separatorAlignment', null);
      if (alignment != null) {

         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in alignment)) {
               switch (alignment[_deviceObj.key]) {
                  case "left":
                     style.addCss('margin-left', '0', _deviceObj.type);
                     style.addCss('margin-right', 'auto', _deviceObj.type);
                     break;
                  case "right":
                     style.addCss('margin-left', 'auto', _deviceObj.type);
                     style.addCss('margin-right', '0', _deviceObj.type);
                     break;
                  case "center":
                     style.addCss('margin-left', 'auto', _deviceObj.type);
                     style.addCss('margin-right', 'auto', _deviceObj.type);
                     break;
               }
            }
         });
      }
      
      return '<hr />';
   }

   window.JDBuilderElementSeparator = JDBuilderElementSeparator;

})();