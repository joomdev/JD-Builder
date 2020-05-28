(function () {

   var JDBuilderElementJoomlaArticles = function (element) {
      var items = [];
      var _data = _JDBDATA.get(element.id);
      if (_data === undefined) {
         items = [];
      } else {
         items = _data.items;
      }
      var categories = element.params.get('categories', []);
      var subcategories = element.params.get('subcategoryArticles', false);
      var articleCount = element.params.get('articleCount', 10);
      var featuredArticle = element.params.get('featuredArticle', 'show');
      var articleOrdering = element.params.get('articleOrdering', 'a.ordering');
      var direction = element.params.get('articleOrderingDirection', 'ASC');
      var viewReadLink = element.params.get('viewReadLink', 0);
      var subcategories = element.params.get('subcategoryArticles', false);
      var dateFormat = element.params.get('metaDateFormat', 'd M, Y');
      return '<div class="jdb-jarticles-live-preview"><div jdb-loader></div><div jdb-jarticles="id:' + element.id + (categories.length ? ';categories:' + categories.join(',') : '') + ';sub:' + (subcategories ? 'true' : 'false') + ';count:' + articleCount + ';featured:' + featuredArticle + ';ordering:' + articleOrdering + ';direction:' + direction + ';viewmore:' + viewReadLink + ';format:' + dateFormat + '">' + render(element, items) + '</div></div>';
   }

   function render(element, items) {
      element.addClass('jdb-joomla-articles');
      var layout = element.params.get('articleLayout', 'grid');
      var rowColsClass = ['jdb-row'];
      if (layout == 'grid') {
         var layoutClass = 'jdb-jarticles jdb-jarticles-grid-view';
         var columns = element.params.get('columns', null);
         if (columns != null) {
            JDBRenderer.DEVICES.forEach(function (_deviceObj) {
               if ((_deviceObj.key in columns) && columns != '') {
                  switch (_deviceObj.type) {
                     case 'desktop':
                        rowColsClass.push('jdb-row-cols-lg-' + columns[_deviceObj.key]);
                        break;
                     case 'tablet':
                        rowColsClass.push('jdb-row-cols-sm-' + columns[_deviceObj.key]);
                        break;
                     case 'mobile':
                        rowColsClass.push('jdb-row-cols-' + columns[_deviceObj.key]);
                        break;
                  }
               }
            });
         }
      } else {
         layoutClass = 'jdb-jarticles jdb-jarticles-list-view';
         if (layout == "list-alternate") {
            layoutClass += ' jdb-jarticles-list-view-alternate';
         }
         rowColsClass.push('jdb-row-cols-1');
      }

      var articleMetaData = element.params.get('articleMetaData', []);
      var mIcon = element.params.get('articleMetaIcons', true);
      var titleTag = element.params.get('titleHtmlTag', 'h2');

      var readMoreText = element.params.get('readmoreText', '');
      var viewmoreText = element.params.get('viewmoreText', '');

      var linkOn = element.params.get('linkOn', 'title');
      var titleLink = (linkOn == 'title' || linkOn == 'title-thumbnail') ? true : false;
      var thumbnailLink = (linkOn == 'thumbnail' || linkOn == 'title-thumbnail') ? true : false;

      var _html = '';
      _html += '<div class="' + layoutClass + '">';
      _html += '<div class="' + rowColsClass.join(' ') + '">';

      items.forEach(function (item) {
         _html += '<div class="jdb-col jdb-jarticle-wrapper">';
         _html += '<article class="jdb-jarticle">';

         if (item.imageSrc != '' && element.params.get('articleThumbnail', true)) {
            _html += '<div class="jdb-jarticle-img-wrap">';
            if (thumbnailLink) {
               _html += '<a title="' + item.title + '" href="' + item.link + '">';
            }
            _html += '<img class="jdb-jarticle-img" src="' + item.imageSrc + '" alt="' + item.imageAlt + '">';
            if (thumbnailLink) {
               _html += '</a>';
            }
            _html += '</div>';
         }
         _html += '<div class="jdb-jarticle-body">';

         _html += '<' + titleTag + ' class="jdb-jarticle-title">';
         if (titleLink) {
            _html += '<a href="' + item.link + '" title="' + item.title + '">';
         } else {
            _html += '<span>';
         }
         _html += item.title;
         if (titleLink) {
            _html += '</a>';
         } else {
            _html += '</span>';
         }
         _html += '</' + titleTag + '>';

         if (articleMetaData.length) {
            _html += '<div class="jdb-jarticle-meta-info">';
            if (articleMetaData.indexOf('author') > -1) {
               _html += '<span class="jdb-jarticle-author">';
               if (mIcon) {
                  _html += '<i class="far fa-user"></i>';
               }
               _html += item.created_by_alias != '' ? item.created_by_alias : item.author;
               _html += '</span>';
            }
            if (articleMetaData.indexOf('category') > -1) {
               _html += '<span class="jdb-jarticle-category">';
               if (mIcon) {
                  _html += '<i class="far fa-folder"></i>';
               }
               _html += item.category_title;
               _html += '</span>';
            }
            if (articleMetaData.indexOf('publish-date') > -1) {
               _html += '<span class="jdb-jarticle-published-date">';
               if (mIcon) {
                  _html += '<i class="far fa-calendar-check"></i>';
               }
               _html += 'Published On: ' + item.published_formatted;
               _html += '</span>';
            }
            if (articleMetaData.indexOf('created-date') > -1) {
               _html += '<span class="jdb-jarticle-created-date">';
               if (mIcon) {
                  _html += '<i class="far fa-calendar-plus"></i>';
               }
               _html += 'Created On: ' + item.created_formatted;
               _html += '</span>';
            }
            if (articleMetaData.indexOf('modified-date') > -1) {
               _html += '<span class="jdb-jarticle-modified-date">';
               if (mIcon) {
                  _html += '<i class="far fa-calendar-alt"></i>';
               }
               _html += 'Modified On: ' + item.modified_formatted;
               _html += '</span>';
            }
            if (articleMetaData.indexOf('hits') > -1) {
               _html += '<span class="jdb-jarticle-hits">';
               if (mIcon) {
                  _html += '<i class="far fa-eye"></i>';
               }
               _html += 'Hits: ' + item.hits;
               _html += '</span>';
            }
            _html += '</div>';
         }

         if (element.params.get('articleIntro', true)) {
            _html += '<p class="jdb-jarticle-introtext">';
            _html += JDBRenderer.Helper.chopString(item.introtext, element.params.get('articleIntroLimit', 150));
            _html += '</p>';
         }

         if (readMoreText != '') {
            _html += JDBRenderer.Helper.renderButtonValue('readmore', element, readMoreText, [], 'link', item.link);
         }

         _html += '</div>';

         _html += '</article>';
         _html += '</div>';
      });

      _html += '</div>';
      _html += '</div>';

      var _data = _JDBDATA.get(element.id);
      if (_data !== undefined && _data.viewmore != '' && viewmoreText != '') {
         var _viewMoreLink = _data.viewmore;
         _html += JDBRenderer.Helper.renderButtonValue('viewmore', element, viewmoreText, [], 'link', _viewMoreLink);
      }
      elementStyling(element);
      return _html;
   }

   function elementStyling(element) {
      var metaStyle = new JDBRenderer.ElementStyle(".jdb-jarticle-meta-info");
      var metaTitleStyle = new JDBRenderer.ElementStyle(".jdb-jarticle-meta-info span:not(:last-child)");
      var metaIconStyle = new JDBRenderer.ElementStyle(".jdb-jarticle-meta-info span i");
      var titleStyle = new JDBRenderer.ElementStyle(".jdb-jarticle-title");
      var titleChildStyle = new JDBRenderer.ElementStyle(".jdb-jarticle-title > span, .jdb-jarticle-title > a");
      var titleChildHoverStyle = new JDBRenderer.ElementStyle(".jdb-jarticle-title > span:hover, .jdb-jarticle-title > a:hover");
      var contentStyle = new JDBRenderer.ElementStyle(".jdb-jarticle-introtext");
      var linkOn = element.params.get('linkOn', 'title');
      var titleLink = (linkOn == 'title' || linkOn == 'title-thumbnail') ? true : false;

      element.addChildrenStyle([metaStyle, metaTitleStyle, metaIconStyle, titleStyle, titleChildStyle, titleChildHoverStyle, contentStyle]);

      // meta styling
      metaStyle.addCss('color', element.params.get('metaColor', ''));
      metaIconStyle.addCss('color', element.params.get('metaIconColor', ''));

      var spacing = element.params.get('metaSpacing', null);
      if (spacing !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if ((_deviceObj.key in spacing) && JDBRenderer.Helper.checkSliderValue(spacing[_deviceObj.key])) {
               metaTitleStyle.addCss('margin-right', spacing[_deviceObj.key].value + 'px', _deviceObj.type);
            }
         });
      }

      var margin = element.params.get('metaMargin', null);
      if (margin != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in margin) {
               metaStyle.addStyle(JDBRenderer.Helper.spacingValue(margin[_deviceObj.key], "margin"), _deviceObj.type);
            }
         });
      }

      var typography = element.params.get('metaTypography', null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               metaStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
               if ('alignment' in typography[_deviceObj.key] && typography[_deviceObj.key].alignment != '') {
                  var justifyContent = typography[_deviceObj.key].alignment;
                  var justifyContent = justifyContent == 'left' ? 'flex-start' : (justifyContent == 'right' ? 'flex-end' : (justifyContent == 'center' ? 'center' : (justifyContent == 'justify' ? 'space-between' : '')));
                  metaStyle.addCss('justify-content', justifyContent, _deviceObj.type);
               }
            }
         });
      }

      // title & content styling
      titleChildStyle.addCss('color', element.params.get('titleColor', ''));
      if (titleLink) {
         titleChildHoverStyle.addCss('color', element.params.get('titleHoverColor', ''));
      }
      contentStyle.addCss('color', element.params.get('contentColor', ''));

      var typography = element.params.get('titleTypography', null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               titleStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      var typography = element.params.get('contentTypography', null);
      if (typography !== null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in typography) {
               contentStyle.addStyle(JDBRenderer.Helper.typographyValue(typography[_deviceObj.key]), _deviceObj.type);
            }
         });
      }

      var margin = element.params.get('titleMargin', null);
      if (margin != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in margin) {
               titleStyle.addStyle(JDBRenderer.Helper.spacingValue(margin[_deviceObj.key], "margin"), _deviceObj.type);
            }
         });
      }

      var margin = element.params.get('contentMargin', null);
      if (margin != null) {
         JDBRenderer.DEVICES.forEach(function (_deviceObj) {
            if (_deviceObj.key in margin) {
               contentStyle.addStyle(JDBRenderer.Helper.spacingValue(margin[_deviceObj.key], "margin"), _deviceObj.type);
            }
         });
      }
   }

   window.JDBuilderElementJoomlaArticles = JDBuilderElementJoomlaArticles;

})();