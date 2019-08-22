(function () {

   var JDBuilderElementAlert = function (element) {
      // Params
      var content = element.params.get("alertContent", "");
      var heading = element.params.get("alertHeading", "");

      if (content == "" && heading == "") {
         return;
      }
      var _class = [];
      var alertHTML = [];

      // Alert Icon
      var icon = element.params.get("alertIcon", "");
      var iconHTML = [];
      if (icon != "") {
         JDBRenderer.Document.loadFontLibraryByIcon(icon);
         iconHTML.push('<span class="jdb-alert-icon ' + icon + '"></span>');
      }

      // Alert Dismiss
      var dismissButton = element.params.get("dismissButton", false);
      var dismissHTML = "";
      if (dismissButton) {
         _class.push('jdb-alert-dismissible');
         dismissHTML = '<a href="#" class="jdb-close jdb-alert-close"><span aria-hidden="true">&times;</span></a>';
      }

      // Alert Heading
      if (heading != '') {
         alertHTML.push('<h4 class="jdb-alert-heading">');
         alertHTML.push(iconHTML.join(""));
         alertHTML.push(heading);
         alertHTML.push('</h4>');
      }

      // Alert Content
      if (content != '') {
         alertHTML.push('<div class="jdb-alert-content">');
         if (heading == '') {
            alertHTML.push(iconHTML);
         }
         alertHTML.push(content);
         alertHTML.push('</div>');
      }

      // Alert Type
      var type = element.params.get("alertType", "success");
      if (type != '') {
         _class.push('jdb-alert-' + type);
      }

      alertHTML.push(dismissHTML);


      var contentStyle = JDBRenderer.ElementStyle('> .jdb-alert > .jdb-alert-content');
      var headingStyle = JDBRenderer.ElementStyle('> .jdb-alert > .jdb-alert-heading');
      element.addChildrenStyle([contentStyle, headingStyle]);

      // content typography
      var typography = element.params.get("alertContentTypography", null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               contentStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      // heading typography
      var typography = element.params.get("alertHeadingTypography", null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               headingStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }


      var _html = [];
      _html.push('<div jdb-alert class="jdb-alert' + (_class.length ? ' ' + _class.join(' ') : '') + '">' + alertHTML.join("") + '</div>');
      return _html.join("");
   }

   window.JDBuilderElementAlert = JDBuilderElementAlert;

})();