(function () {

   var JDBuilderElementEmpty = function (element) {

      var space = element.params.get('space', null);

      if (space !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in space) && JDBRenderer.Helper.checkSliderValue(space[_deviceObj.key])) {
               element.addCss("height", space[_deviceObj.key].value + space[_deviceObj.key].unit, _deviceObj.type);
            }
         });
      }
   };

   window.JDBuilderElementEmpty = JDBuilderElementEmpty;

})();