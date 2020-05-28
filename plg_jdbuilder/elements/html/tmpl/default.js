(function () {

   var JDBuilderElementHtml = function (element) {
      var content = element.params.get('code', '', 'RAW');
      if (content == '') {
         return;
      }
      element.addClass('jdb-html');
      let _html = [];
      _html.push('<div class="jdb-html-content">' + content + '</div>');
      return _html.join('');
   }

   window.JDBuilderElementHtml = JDBuilderElementHtml;

})();