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
            } catch (e) {}
            links[i].addEventListener('click', this.disableLinkRef, true);
         }
      };

      _proto.clearParticles = function clearParticles() {
         if (window["pJSDom"] instanceof Array && window["pJSDom"].length > 0) {
            for (let i = 0; i < window["pJSDom"].length; i++) {
               cancelAnimationFrame(window["pJSDom"][i].pJS.fn.drawAnimFrame);
            }
         }
         $('.particles-js-canvas-el').remove();
         window["pJSDom"] = [];
      };

      _proto.makeParticles = function makeParticles() {
         document.querySelectorAll('.jdb-particles').forEach(function (_el) {
            JDBPack.particles(_el).init();
         });
      };

      return JDBPreview;
   }();


var iPreview = new JDBPreview();

function initDisableLinkRef() {
   iPreview.initDisableLinkRef();
}

/* function clearParticles() {
   iPreview.clearParticles();
}

function makeParticles() {
   iPreview.makeParticles();
} */

function editJDBElement(_id) {
   window.parent.$(window.parent.document).trigger('jdb-edit-element', [_id]);
}

(function ($) {
   $(function () {
      initDisableLinkRef();
   });
})(jQuery);