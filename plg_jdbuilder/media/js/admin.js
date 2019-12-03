var JDBAppLoader = function JDBAppLoader() {
   this.loader = document.getElementById("loading-value");
   this.interval = null;
   this.sInterval = null;
   this.value = 0;
   this.logoLoadStatus = false;

   this.statuses = [
      'Fighting Global Warming',
      'Petting Cats',
      'Pulling Carbon from the atmosphere',
      'Spinning up the hamster',
      'Ordering 1s and 0s',
      'Downloading more RAM',
      'The internet is full. Please wait',
      'Loading the Loading message',
      'Making stuff up. Please wait'
   ];

   this.usedStatuses = [];

   this.start = function () {
      var _this = this;
      this.statuses = this.shuffle(this.statuses);
      _this.status(_this.getStatus());

      _this.interval = setInterval(function () {
         _this.loading();
      }, 180);

      _this.sInterval = setInterval(function () {
         _this.status(_this.getStatus());
      }, 1500);
   };

   this.status = function (message) {
      if (message == false) {
         return;
      }
      document.getElementById('jdbuilder-apploader-status').innerHTML = message;
   };

   this.getStatus = function () {
      if (this.statuses.length < 1) {
         return false;
      }
      return this.statuses.pop();
   }

   this.shuffle = function (array) {
      var currentIndex = array.length, temporaryValue, randomIndex;

      // While there remain elements to shuffle...
      while (0 !== currentIndex) {

         // Pick a remaining element...
         randomIndex = Math.floor(Math.random() * currentIndex);
         currentIndex -= 1;

         // And swap it with the current element.
         temporaryValue = array[currentIndex];
         array[currentIndex] = array[randomIndex];
         array[randomIndex] = temporaryValue;
      }

      return array;
   }

   this.loading = function () {
      this.loader.style.width = this.value + "%";
      if (this.value >= 100) {
         clearInterval(this.interval);
         clearInterval(this.sInterval);
         this.done();
      }
   };

   this.removeClass = function (el, className)
   {
      if (el.classList)
         el.classList.remove(className)
      else if (hasClass(el, className))
      {
         var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
         el.className = el.className.replace(reg, ' ');
      }
   };

   this.done = function () {
      var _this = this;
      document.getElementById('jdbuilder-apploader-container').style.color = '#323896';
      setTimeout(function () {
         document.getElementById('jdbuilder-app-loader').style.display = "none";
         _this.removeClass(document.getElementById('jdbuilder-area'), 'loading');
      }, 500);
   };
};

var JDLogger = function JDLogger() {
   var _this = this;
   _this.debug = true;
   _this.lastlog = null;
   _this.lastlogtype = null;
   _this.recordings = [];
   _this.log = function (_message, _type) {
      if (!_this.debug) {
         return false;
      }
      _this.lastlogtype = _type;
      _this.lastlog = Date.now();
      var _color = "#9c9abd";
      switch (_type) {
         case "success":
            _color = "#84d155";
            _message = "✓ " + _message;
            break;
         case "error":
            _color = "#dc3545";
            _message = "✗ " + _message;
            break;
         case "love":
            _color = "#dc3545";
            _message = "♥ " + _message;
            break;
         case "warning":
            _color = "#f5e02a";
            _message = "⚠ " + _message;
            break;
         case "info":
            _color = "#2196f3";
            _message = "→ " + _message;
            break;
         case "xhr":
            _color = "#ff74a3";
            _message = "→ " + _message;
            break;
         case "important":
            _color = "#f07339";
            _message = "→ " + _message;
            break;
         case "primary":
            _color = "#464ed2";
            break;
         case "action":
            _color = "#464ed2";
            _message = "→ " + _message;
            break;
      }
      console.log("%c" + _message, "color:" + _color + ";");
   }

   _this.table = function (_data) {
      if (!_this.debug) {
         return false;
      }
      console.table(_data);
   }

   _this.start = function (_title) {
      if (!_this.debug) {
         return false;
      }
      _title = _title + ' Load Time';
      console.time(_title);
      _this.recordings.push(_title);
   }

   _this.stop = function (_title) {
      if (!_this.debug) {
         return false;
      }
      _this.lastlog = Date.now();
      _title = _title + ' Load Time';
      console.timeEnd(_title);
      var _index = _this.recordings.indexOf(_title);
      if (_index >= 0) {
         _this.recordings.splice(_index, 1);
      }
   }

   _this.stopAll = function () {
      if (!_this.debug) {
         return false;
      }
      _this.lastlog = Date.now();
      _this.recordings.forEach(function (_title) {
         console.timeEnd(_title);
      });
      _this.recordings = [];
   }

   _this.size = function (obj) {
      if (!_this.debug) {
         return false;
      }
      var bytes = 0;
      if (obj !== null && obj !== undefined) {
         switch (typeof obj) {
            case 'number':
               bytes += 8;
               break;
            case 'string':
               bytes += obj.length * 2;
               break;
            case 'boolean':
               bytes += 4;
               break;
            case 'object':
               var objClass = Object.prototype.toString.call(obj).slice(8, -1);
               if (objClass === 'Object' || objClass === 'Array') {
                  for (var key in obj) {
                     if (!obj.hasOwnProperty(key))
                        continue;
                     sizeOf(obj[key]);
                  }
               } else
                  bytes += obj.toString().length * 2;
               break;
         }
      }
      return _this.formatByteSize(bytes);
   }

   _this.formatByteSize = function (bytes) {
      if (bytes < 1024)
         return bytes + " bytes";
      else if (bytes < 1048576)
         return(bytes / 1024).toFixed(3) + " KB";
      else if (bytes < 1073741824)
         return(bytes / 1048576).toFixed(3) + " MB";
      else
         return(bytes / 1073741824).toFixed(3) + " GB";
   }
};

var JDBAdmin = function () {
   var _this = this;
   _this.init = function () {
      $(window).scroll(() => {
         var _body = $('.builder-admin-body.non-fullscreen');
         if (_body.length && (($(window).scrollTop() + 11) > _body.offset().top)) {
            $('.page-options-toolbar').addClass('top-fixed');
            _body.addClass('top-margin');
         } else {
            $('.page-options-toolbar').removeClass('top-fixed');
            _body.removeClass('top-margin');
         }
      });
   }
}