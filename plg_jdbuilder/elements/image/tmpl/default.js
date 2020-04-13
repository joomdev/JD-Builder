(function () {

   var JDBuilderElementImage = function (element) {

      // params
      var attrs = [];

      // image source
      var image = element.params.get('image', '');
      if (image == '') {
         return;
      }

      // image title and alt
      var imageTitle = element.params.get('title', '');
      var imageAlt = element.params.get('alt', '');
      if (imageTitle != '') {
         attrs.push('title="' + imageTitle + '"');
      }
      if (imageAlt != '') {
         attrs.push('alt="' + imageAlt + '"');
      }

      // image caption
      var caption = element.params.get('caption', '');
      var settings = JDBRenderer.Helper.globalSettings();
      var lightbox = element.params.get('lightbox', '');
      lightbox = lightbox == '' ? (settings.get('lightbox', true) == 'true' ? true : false) : (Number(lightbox) ? true : false);

      // link
      var linkType = element.params.get("linkType", "");
      if (linkType == 'none') {
         var link = "";
      } else if (linkType == 'media') {
         var link = JDBRenderer.Helper.mediaValue(image);
         var linkTarget = ' target="_blank"';
         var linkRel = "";
         var linkLightbox = "";

         if (lightbox) {
            linkLightbox = ' data-jdb-lightbox="lightbox-' + element.id + '" data-jdb-lightbox-caption="' + JDBRenderer.Helper.getLightboxContent('description', imageTitle, caption, imageAlt) + '" data-jdb-lightbox-title="' + JDBRenderer.Helper.getLightboxContent('title', imageTitle, caption, imageAlt) + '"';
         }
      } else {
         var link = element.params.get("link", "");

         var linkTargetBlank = element.params.get('linkTargetBlank', false);
         var linkTarget = linkTargetBlank ? ' target="_blank"' : "";

         var linkNoFollow = element.params.get('linkNoFollow', false);
         var linkRel = linkNoFollow ? ' rel="nofollow"' : "";
         var linkLightbox = "";
      }


      // attributes, classes and styles
      element.addClass('jdb-image');

      attrs = attrs.length ? attrs.join(' ') : '';


      imageStyling(element);

      var _html = [];

      _html.push('<figure class="jdb-image-wrapper">');
      if (link != '') {
         _html.push('<a class="jdb-image-link" href="' + link + '"' + linkTarget + linkRel + linkLightbox + '>');
      }
      _html.push('<img src="' + JDBRenderer.Helper.mediaValue(image) + '"' + attrs + ' />');
      if (caption != '') {
         element.addClass('has-caption');
         _html.push('<figcaption class="jdb-image-caption">' + caption + '</figcaption>');
      }
      if (link != '') {
         _html.push('</a>');
      }
      _html.push('</figure>');
      return _html.join('');
   };

   function imageStyling(element) {
      // Image Alignment
      var alignment = element.params.get('alignment', null);
      JDBRenderer.DEVICES.forEach(function (_deviceObj) {
         if ((_deviceObj.key in alignment) && alignment[_deviceObj.key] != '' && alignment[_deviceObj.key] != null) {
            element.addCss("text-align", alignment[_deviceObj.key], _deviceObj.type);
         }
      });

      var imageStyle = JDBRenderer.ElementStyle('img');
      element.addChildStyle(imageStyle);

      if (element.params.get('imageSize', 'original') == 'custom') {
         var width = element.params.get('width', null);
         if (width != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if ((_deviceObj.key in width) && JDBRenderer.Helper.checkSliderValue(width[_deviceObj.key])) {
                  imageStyle.addCss("width", width[_deviceObj.key].value + width[_deviceObj.key].unit, _deviceObj.type);
               }
            });
         }

         var width = element.params.get('maxWidth', null);
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in width) && JDBRenderer.Helper.checkSliderValue(width[_deviceObj.key])) {
               imageStyle.addCss("max-width", width[_deviceObj.key].value + width[_deviceObj.key].unit, _deviceObj.type);
            }
         });

      }
      JDBRenderer.Helper.applyBorderValue(imageStyle, element.params, "imageBorder");
   }

   window.JDBuilderElementImage = JDBuilderElementImage;

})();