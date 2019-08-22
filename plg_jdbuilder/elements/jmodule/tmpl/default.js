(function () {

   var JDBuilderElementJmodule = function (element) {

      element.addClass('jdb-module');
      var type = element.params.get('type', 'module');
      var value = element.params.get(type, '');

      let _html = [];
      if (value != '') {
         element.addClass('well');
         if (type == 'position') {
            _html.push("Module Position `<code>" + value + "</code>` will render here.");
         } else {
            _html.push("Module ID `<code>" + value + "</code>` will render here.");
         }
      }
      return _html.join("");
   }

   window.JDBuilderElementJmodule = JDBuilderElementJmodule;

})();