"use strict";

var JDBPreview =
        /*#__PURE__*/
                function () {
                   function JDBPreview() {}

                   var _proto = JDBPreview.prototype;

                   _proto.disableLinkRef = function disableLinkRef(event) {
                      event.preventDefault();
                   };

                   _proto.initDisableLinkRef = function initDisableLinkRef() {
                      var links = document.getElementsByTagName('a');
                      for (var i = 0; i < links.length; i++) {
                         try {
                            links[i].removeEventListener('click', this.disableLinkRef, true);
                         } catch (e) {
                         }
                         links[i].addEventListener('click', this.disableLinkRef, true);
                      }
                   };

                   return JDBPreview;
                }();


        var iPreview = new JDBPreview();
        function initDisableLinkRef() {
           iPreview.initDisableLinkRef();
        }
        function editJDBElement(_id) {
           window.parent.$(window.parent.document).trigger('jdb-edit-element',[_id]);
        }
        (function ($) {
           $(function () {
              initDisableLinkRef();
           });
        })(jQuery);