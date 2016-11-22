(function() {
  var indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  angular.module("Egerep", ['ngResource', 'angular-ladda', 'angular-inview', 'angularFileUpload', 'angular-toArrayFilter']).config([
    '$compileProvider', function($compileProvider) {
      return $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/);
    }
  ]).config(function(laddaProvider) {
    return laddaProvider.setOption({
      spinnerColor: '#83b060'
    });
  }).filter('cut', function() {
    return function(value, wordwise, max, nothing, tail) {
      var lastspace;
      if (nothing == null) {
        nothing = '';
      }
      if (!value) {
        return nothing;
      }
      max = parseInt(max, 10);
      if (!max) {
        return value;
      }
      if (value.length <= max) {
        return value;
      }
      value = value.substr(0, max);
      if (wordwise) {
        lastspace = value.lastIndexOf(' ');
        if (lastspace !== -1) {
          if (value.charAt(lastspace - 1) === '.' || value.charAt(lastspace - 1) === ',') {
            lastspace = lastspace - 1;
          }
          value = value.substr(0, lastspace);
        }
      }
      return value + (tail || '…');
    };
  }).filter('hideZero', function() {
    return function(item) {
      if (item > 0) {
        return item;
      } else {
        return null;
      }
    };
  }).run(function($rootScope, $q) {
    $rootScope.dataLoaded = $q.defer();
    $rootScope.frontendStop = function(rebind_masks) {
      if (rebind_masks == null) {
        rebind_masks = true;
      }
      $rootScope.frontend_loading = false;
      $rootScope.dataLoaded.resolve(true);
      if (rebind_masks) {
        return rebindMasks();
      }
    };
    $rootScope.range = function(min, max, step) {
      var i, input;
      step = step || 1;
      input = [];
      i = min;
      while (i <= max) {
        input.push(i);
        i += step;
      }
      return input;
    };
    $rootScope.toggleEnum = function(ngModel, status, ngEnum, skip_values, allowed_user_ids, recursion) {
      var ref, ref1, ref2, status_id, statuses;
      if (skip_values == null) {
        skip_values = [];
      }
      if (allowed_user_ids == null) {
        allowed_user_ids = [];
      }
      if (recursion == null) {
        recursion = false;
      }
      if (!recursion && (ref = parseInt(ngModel[status]), indexOf.call(skip_values, ref) >= 0) && (ref1 = $rootScope.$$childHead.user.id, indexOf.call(allowed_user_ids, ref1) < 0)) {
        return;
      }
      statuses = Object.keys(ngEnum);
      status_id = statuses.indexOf(ngModel[status].toString());
      status_id++;
      if (status_id > (statuses.length - 1)) {
        status_id = 0;
      }
      ngModel[status] = statuses[status_id];
      if (indexOf.call(skip_values, status_id) >= 0 && (ref2 = $rootScope.$$childHead.user.id, indexOf.call(allowed_user_ids, ref2) < 0)) {
        return $rootScope.toggleEnum(ngModel, status, ngEnum, skip_values, allowed_user_ids, true);
      }
    };
    $rootScope.toggleEnumServer = function(ngModel, status, ngEnum, Resource) {
      var status_id, statuses, update_data;
      statuses = Object.keys(ngEnum);
      status_id = statuses.indexOf(ngModel[status].toString());
      status_id++;
      if (status_id > (statuses.length - 1)) {
        status_id = 0;
      }
      update_data = {
        id: ngModel.id
      };
      update_data[status] = status_id;
      return Resource.update(update_data, function() {
        return ngModel[status] = statuses[status_id];
      });
    };
    $rootScope.formatDateTime = function(date) {
      return moment(date).format("DD.MM.YY в HH:mm");
    };
    $rootScope.formatDate = function(date, full_year) {
      if (full_year == null) {
        full_year = false;
      }
      if (!date) {
        return '';
      }
      return moment(date).format("DD.MM.YY" + (full_year ? "YY" : ""));
    };
    $rootScope.formatDateFull = function(date) {
      return moment(date).format("D MMMM YYYY");
    };
    $rootScope.dialog = function(id) {
      $("#" + id).modal('show');
    };
    $rootScope.closeDialog = function(id) {
      $("#" + id).modal('hide');
    };
    $rootScope.onEnter = function(event, fun, prevent_default) {
      if (prevent_default == null) {
        prevent_default = true;
      }
      if (prevent_default) {
        event.preventDefault();
      }
      if (event.keyCode === 13) {
        return fun();
      }
    };
    $rootScope.ajaxStart = function() {
      ajaxStart();
      return $rootScope.saving = true;
    };
    $rootScope.ajaxEnd = function() {
      ajaxEnd();
      return $rootScope.saving = false;
    };
    $rootScope.findById = function(object, id) {
      return _.findWhere(object, {
        id: parseInt(id)
      });
    };
    $rootScope.total = function(array, prop, prop2) {
      var sum;
      if (prop2 == null) {
        prop2 = false;
      }
      sum = 0;
      $.each(array, function(index, value) {
        var v;
        v = value[prop];
        if (prop2) {
          v = v[prop2];
        }
        return sum += v;
      });
      return sum;
    };
    $rootScope.yearsPassed = function(year) {
      return moment().format("YYYY") - year;
    };
    $rootScope.deny = function(ngModel, prop) {
      return ngModel[prop] = +(!ngModel[prop]);
    };
    $rootScope.closestMetro = function(markers) {
      var closest_metro;
      closest_metro = markers[0].metros[0];
      markers.forEach(function(marker) {
        return marker.metros.forEach(function(metro) {
          if (metro.minutes < closest_metro.minutes) {
            return closest_metro = metro;
          }
        });
      });
      return closest_metro.station.title;
    };
    return $rootScope.formatBytes = function(bytes) {
      if (bytes < 1024) {
        return bytes + ' Bytes';
      } else if (bytes < 1048576) {
        return (bytes / 1024).toFixed(1) + ' KB';
      } else if (bytes < 1073741824) {
        return (bytes / 1048576).toFixed(1) + ' MB';
      } else {
        return (bytes / 1073741824).toFixed(1) + ' GB';
      }
    };
  });

}).call(this);

(function() {


}).call(this);

(function() {
  angular.module('Egerep').controller('Cv', function($scope, $timeout, Tutor, FileUploader, Cv, PhoneService) {
    bindArguments($scope, arguments);
    $scope.error_text = 'ошибка: максимальная длина текста – 1000 символов';
    $timeout(function() {
      return console.log(1);
    }, 1000);
    $scope.application = {
      agree_to_publish: 1
    };
    FileUploader.FileSelect.prototype.isEmptyAfterSelection = function() {
      return true;
    };
    $scope.uploader = new FileUploader({
      url: 'api/cv/uploadPhoto',
      alias: 'photo',
      autoUpload: true,
      method: 'post',
      removeAfterUpload: true,
      onProgressItem: function(i, progress) {
        return $scope.percentage = progress;
      },
      onCompleteItem: function(i, response, status) {
        $scope.percentage = void 0;
        if (status === 200) {
          $scope.application.filename = response.filename;
          return $scope.application.filesize = response.size;
        } else {
          $scope.upload_error = true;
          return $scope.application.filename = void 0;
        }
      }
    });
    $scope.clearFile = function() {
      $scope.upload_error = false;
      return $scope.application.filename = void 0;
    };
    $scope.upload = function(e) {
      e.preventDefault();
      $scope.upload_error = false;
      $('#upload-button').trigger('click');
      return false;
    };
    return $scope.sendApplication = function() {
      return Cv.save($scope.application, function() {
        return $scope.application.sent = true;
      }, function(response) {
        if (response.status === 422) {
          $scope.errors = {};
          return angular.forEach(response.data, function(errors, field) {
            var selector;
            $scope.errors[field] = errors;
            selector = "[ng-model$='" + field + "']";
            return $("input" + selector + ", textarea" + selector).focus();
          });
        } else {
          return $scope.application.error = true;
        }
      });
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').controller('Index', function($scope, $timeout, $http, Tutor) {
    bindArguments($scope, arguments);
    $scope.selected_subject = '1';
    $scope.refreshSelect = function() {
      return $timeout(function() {
        return $('.custom-select-sort').trigger('render');
      });
    };
    $scope.goSubject = function() {
      return window.location = $scope.subject_routes[$scope.selected_subject];
    };
    $scope.dateToText = function(date) {
      var text_date;
      text_date = moment(date).format('DD MMMM YYYY');
      return text_date.substr(3);
    };
    return $scope.randomReview = function() {
      return $http.get('api/reviews/random').then(function(response) {
        return $scope.random_review = response.data;
      });
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').controller('LoginCtrl', function($scope, $timeout, Sms, Tutor) {
    var login;
    bindArguments($scope, arguments);
    login = function() {
      return Tutor.login({}, function(response) {
        return $scope.tutor = response;
      }, function() {
        return $scope.tutor = null;
      });
    };
    login();
    $scope.sendCode = function() {
      $scope.error_message = false;
      return Sms.save({
        phone: $scope.phone
      }, function() {
        $scope.code_sent = true;
        return $timeout(function() {
          return $('#code-input').focus();
        });
      }, function() {
        return $scope.error_message = 'неверный номер телефона';
      });
    };
    return $scope.checkCode = function() {
      $scope.error_message = false;
      return Sms.get({
        code: $scope.code
      }, function() {
        return login();
      }, function(response) {
        if (response.status === 403) {
          redirect('/');
        }
        return $scope.error_message = 'код введен неверно';
      });
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').constant('REVIEWS_PER_PAGE', 5).controller('Tutors', function($scope, $timeout, Tutor, SubjectService, REVIEWS_PER_PAGE, Request) {
    var filter_used, highlight, search, search_count, unselectSubjects, viewed_tutors;
    bindArguments($scope, arguments);
    search_count = 0;
    $scope.profilePage = function() {
      return RegExp(/^\/[\d]+$/).test(window.location.pathname);
    };
    if (!$scope.profilePage()) {
      $timeout(function() {
        if ($scope.selected_subjects) {
          $scope.selected_subjects.split(',').forEach(function(subject_id) {
            return $scope.search.subjects[subject_id] = true;
          });
        }
        SubjectService.init($scope.search.subjects);
        if ($scope.mobile) {
          return $scope.filter();
        } else {
          $scope.chunked_subjects = chunk(toArray($scope.subjects), 4);
          metroAutocomplete($scope);
          if (!parseInt($scope.search.station_id)) {
            return $scope.filter();
          }
        }
      });
    }
    $scope.pairs = [[1, 2], [3, 4], [6, 7], [8, 9]];
    viewed_tutors = [];
    $scope.dateToText = function(date) {
      var text_date;
      text_date = moment(date).format('DD MMMM YYYY');
      return text_date.substr(3);
    };
    $scope.requestSent = function(tutor) {
      return tutor.request_sent || $scope.sent_ids.indexOf(tutor.id) !== -1;
    };
    $scope.gmap = function(tutor) {
      if (tutor.map_shown === void 0) {
        $timeout(function() {
          var bounds, extendPoint1, extendPoint2, map;
          map = new google.maps.Map(document.getElementById("gmap-" + tutor.id), {
            center: MAP_CENTER,
            scrollwheel: false,
            zoom: 8,
            disableDefaultUI: true,
            clickableLabels: false,
            clickableIcons: false,
            zoomControl: true,
            zoomControlOptions: {
              position: google.maps.ControlPosition.LEFT_BOTTOM
            },
            scaleControl: true
          });
          bounds = new google.maps.LatLngBounds;
          tutor.markers.forEach(function(marker) {
            var new_marker;
            bounds.extend(new google.maps.LatLng(marker.lat, marker.lng));
            return new_marker = newMarker(new google.maps.LatLng(marker.lat, marker.lng), map);
          });
          if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
            extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.02, bounds.getNorthEast().lng() + 0.02);
            extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.02, bounds.getNorthEast().lng() - 0.02);
            bounds.extend(extendPoint1);
            bounds.extend(extendPoint2);
          }
          map.fitBounds(bounds);
          map.panToBounds(bounds);
          map.setZoom(12);
          return google.maps.event.addListenerOnce(map, 'idle', function() {
            return $('div:has(>a[href^="https://www.google.com/maps"])').remove();
          });
        });
      }
      return $scope.toggleShow(tutor, 'map_shown', 'gmap');
    };
    $scope.getMetros = function(tutor) {
      return _.chain(tutor.markers).pluck('metros').flatten().value();
    };
    $scope.reviews = function(tutor) {
      if (tutor.all_reviews === void 0) {
        tutor.all_reviews = Tutor.reviews({
          id: tutor.id
        }, function(response) {
          return $scope.showMoreReviews(tutor);
        });
      }
      return $scope.toggleShow(tutor, 'show_reviews', 'reviews');
    };
    $scope.showMoreReviews = function(tutor) {
      var from, to;
      if (tutor.reviews_page) {
        Tutor.iteraction({
          id: tutor.id,
          type: 'reviews_more'
        });
      }
      tutor.reviews_page = !tutor.reviews_page ? 1 : tutor.reviews_page + 1;
      from = (tutor.reviews_page - 1) * REVIEWS_PER_PAGE;
      to = from + REVIEWS_PER_PAGE;
      tutor.displayed_reviews = tutor.all_reviews.slice(0, to);
      return highlight('search-result-reviews-text');
    };
    $scope.reviewsLeft = function(tutor) {
      var reviews_left;
      if (!tutor.all_reviews || !tutor.displayed_reviews) {
        return;
      }
      reviews_left = tutor.all_reviews.length - tutor.displayed_reviews.length;
      if (reviews_left > REVIEWS_PER_PAGE) {
        return REVIEWS_PER_PAGE;
      } else {
        return reviews_left;
      }
    };
    $scope.countView = function(tutor_id) {
      if (viewed_tutors.indexOf(tutor_id) === -1) {
        return viewed_tutors.push(tutor_id);
      }
    };
    filter_used = false;
    $scope.filter = function(subject_id) {
      $scope.tutors = [];
      unselectSubjects(subject_id);
      $scope.page = 1;
      search();
      if ($scope.search.hidden_filter && search_count) {
        delete $scope.search.hidden_filter;
      }
      return filter_used = true;
    };
    $scope.nextPage = function() {
      $scope.page++;
      return search();
    };
    $scope.isLastPage = function() {
      if (!$scope.data) {
        return;
      }
      return $scope.data.current_page >= $scope.data.last_page;
    };
    unselectSubjects = function(subject_id) {
      if (subject_id) {
        return angular.forEach($scope.search.subjects, function(enabled, id) {
          var pair;
          pair = _.filter(scope.pairs, function(p) {
            return p.indexOf(parseInt(subject_id)) !== -1;
          });
          if (!pair.length) {
            pair.push([subject_id]);
          }
          if (pair[0].indexOf(parseInt(id)) === -1) {
            return $scope.search.subjects[id] = false;
          }
        });
      }
    };
    search = function() {
      $scope.searching = true;
      return Tutor.search({
        filter_used: filter_used,
        page: $scope.page,
        search: $scope.search
      }, function(response) {
        search_count++;
        $scope.searching = false;
        if (response.hasOwnProperty('url')) {
          return redirect(response.url);
        } else {
          $scope.data = response;
          $scope.tutors = $scope.tutors.concat(response.data);
          angular.forEach($scope.tutors, function(tutor) {
            if ('string' === typeof tutor.svg_map) {
              return tutor.svg_map = _.filter(tutor.svg_map.split(','));
            }
          });
          highlight('search-result-text');
          if ($scope.mobile) {
            return $timeout(function() {
              return bindToggle();
            });
          }
        }
      });
    };
    highlight = function(className) {
      if ($scope.search && $scope.search.hidden_filter) {
        return $timeout(function() {
          return $.each($scope.search.hidden_filter, function(index, phrase) {
            return $("." + className).mark(phrase, {
              separateWordSearch: true,
              accuracy: {
                value: 'exactly',
                limiters: ['!', '@', '#', '&', '*', '(', ')', '-', '–', '—', '+', '=', '[', ']', '{', '}', '|', ':', ';', '\'', '\"', '‘', '’', '“', '”', ',', '.', '<', '>', '/', '?']
              }
            });
          });
        });
      }
    };
    $scope.clearMetro = function() {
      $('.search-metro-autocomplete').val('');
      $('.search-filter-metro-wrap').removeClass('active');
      $scope.search.station_id = 0;
      $scope.search.sort = '1';
      $scope.filter();
      return $timeout(function() {
        return $('.custom-select-sort').trigger('render');
      });
    };
    $scope.showSvg = function(tutor) {
      var map;
      if (tutor.show_svg === void 0) {
        map = new SVGMap({
          iframeId: 'svg-iframe-' + tutor.id,
          clicable: false,
          places: $scope.svg
        });
        map.init();
        map.deselectAll();
        map.select(tutor.svg_map);
        if ($scope.mobile) {
          $timeout(function() {
            var svg;
            svg = $("#svg-" + tutor.id);
            return svg.scrollLeft(svg.width() / 4).scrollTop(svg.height() / 2);
          });
        }
      }
      return $scope.toggleShow(tutor, 'show_svg', 'svg_map');
    };
    $scope.toggleShow = function(tutor, prop, iteraction_type) {
      if (tutor[prop]) {
        return $timeout(function() {
          return tutor[prop] = false;
        }, $scope.mobile ? 400 : 0);
      } else {
        tutor[prop] = true;
        return Tutor.iteraction({
          id: tutor.id,
          type: iteraction_type
        });
      }
    };
    $scope.overlay = {};
    $scope.changeFilter = function(param, value) {
      if (value == null) {
        value = null;
      }
      if (value !== null) {
        $scope.search[param] = value;
      }
      $scope.overlay[param] = false;
      return $scope.filter();
    };
    $scope.requestDialog = function(tutor) {
      $scope.sending_tutor = tutor;
      return $scope.overlay.request = true;
    };
    $scope.sendRequest = function() {
      if ($scope.sending_tutor.request === void 0) {
        $scope.sending_tutor.request = {};
      }
      $scope.sending_tutor.request.tutor_id = $scope.sending_tutor.id;
      return Request.save($scope.sending_tutor.request, function() {
        return $scope.sending_tutor.request_sent = true;
      }, function(response) {
        if (response.status === 422) {
          return angular.forEach(response.data, function(errors, field) {
            var selector;
            selector = "[ng-model$='" + field + "']";
            return $('.request-overlay').find("input" + selector + ", textarea" + selector).focus().notify(errors[0], notify_options);
          });
        } else {
          return $scope.sending_tutor.request_error = true;
        }
      });
    };
    return angular.element(document).ready(function() {
      if ($scope.mobile) {
        return $timeout(function() {
          return bindToggle();
        });
      }
    });
  });

}).call(this);

(function() {


}).call(this);

(function() {


}).call(this);

(function() {
  angular.module('Egerep').directive('errors', function() {
    return {
      restrict: 'E',
      templateUrl: 'directives/errors',
      scope: {
        model: '@'
      },
      controller: function($scope, $element, $attrs) {
        $scope.only_first = $attrs.hasOwnProperty('onlyFirst');
        return $scope.getErrors = function() {
          var errors;
          if ($scope.$parent.errors === void 0) {
            return;
          }
          errors = $scope.$parent.errors[$scope.model];
          if ($scope.only_first) {
            return [errors[0]];
          } else {
            return errors;
          }
        };
      }
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').directive('icheck', function($timeout, $parse) {
    return {
      require: 'ngModel',
      link: function($scope, element, $attrs, ngModel) {
        return $timeout(function() {
          var value;
          value = void 0;
          value = $attrs['value'];
          $scope.$watch($attrs['ngModel'], function(newValue) {
            $(element).iCheck('update');
          });
          return $(element).iCheck({
            checkboxClass: 'custom-checkbox',
            radioClass: 'custom-radio',
            checkedClass: 'checked',
            cursor: true
          }).on('ifChanged', function(event) {
            if ($(element).attr('type') === 'checkbox' && $attrs['ngModel']) {
              $scope.$apply(function() {
                return ngModel.$setViewValue(event.target.checked);
              });
            }
            if ($(element).attr('type') === 'radio' && $attrs['ngModel']) {
              return $scope.$apply(function() {
                return ngModel.$setViewValue(value);
              });
            }
          });
        });
      }
    };
  });

}).call(this);

(function() {


}).call(this);

(function() {


}).call(this);

(function() {


}).call(this);

(function() {
  angular.module('Egerep').directive('ngPhone', function() {
    return {
      restrict: 'A',
      link: function($scope, element) {
        return $(element).mask("+7 (999) 999-99-99", {
          autoclear: false
        });
      }
    };
  });

}).call(this);

(function() {


}).call(this);

(function() {
  angular.module('Egerep').directive('plural', function() {
    return {
      restrict: 'E',
      scope: {
        count: '=',
        type: '@',
        noneText: '@'
      },
      templateUrl: '/directives/plural',
      controller: function($scope, $element, $attrs, $timeout) {
        $scope.textOnly = $attrs.hasOwnProperty('textOnly');
        $scope.hideZero = $attrs.hasOwnProperty('hideZero');
        return $scope.when = {
          'age': ['год', 'года', 'лет'],
          'student': ['ученик', 'ученика', 'учеников'],
          'minute': ['минуту', 'минуты', 'минут'],
          'hour': ['час', 'часа', 'часов'],
          'day': ['день', 'дня', 'дней'],
          'meeting': ['встреча', 'встречи', 'встреч'],
          'score': ['балл', 'балла', 'баллов'],
          'rubbles': ['рубль', 'рубля', 'рублей'],
          'lesson': ['занятие', 'занятия', 'занятий'],
          'client': ['клиент', 'клиента', 'клиентов'],
          'mark': ['оценки', 'оценок', 'оценок'],
          'review': ['отзыв', 'отзыва', 'отзывов'],
          'request': ['заявка', 'заявки', 'заявок'],
          'station': ['станцию', 'станции', 'станций'],
          'tutor': ['репетитор', 'репетитора', 'репетиторов'],
          'profile': ['анкета', 'анкеты', 'анкет'],
          'schooler': ['школьник нашел', 'школьника нашли', 'школьников нашли'],
          'taught': ['Обучен', 'Обучено', 'Обучено']
        };
      }
    };
  });

}).call(this);

(function() {


}).call(this);

(function() {
  angular.module('Egerep').directive('requestFormMobile', function() {
    return {
      replace: true,
      scope: {
        tutor: '=',
        sentIds: '='
      },
      templateUrl: 'directives/request-form-mobile',
      controller: function($scope, $element, $timeout, Request, RequestService) {
        return $scope.request = function() {
          return RequestService.request($scope.tutor, $element);
        };
      }
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').directive('requestForm', function() {
    return {
      replace: true,
      scope: {
        tutor: '=',
        sentIds: '='
      },
      templateUrl: 'directives/request-form',
      controller: function($scope, $element, $timeout, Request) {
        var trackDataLayer;
        $scope.request = function() {
          if ($scope.tutor.request === void 0) {
            $scope.tutor.request = {};
          }
          $scope.tutor.request.tutor_id = $scope.tutor.id;
          return Request.save($scope.tutor.request, function() {
            $scope.tutor.request_sent = true;
            return trackDataLayer();
          }, function(response) {
            if (response.status === 422) {
              return angular.forEach(response.data, function(errors, field) {
                var selector;
                selector = "[ng-model$='" + field + "']";
                return $($element).find("input" + selector + ", textarea" + selector).focus().notify(errors[0], notify_options);
              });
            } else {
              return $scope.tutor.request_error = true;
            }
          });
        };
        return trackDataLayer = function() {
          window.dataLayer = window.dataLayer || [];
          return window.dataLayer.push({
            event: 'purchase',
            ecommerce: {
              currencyCode: 'RUR',
              purchase: {
                actionField: {
                  id: $scope.tutor.id,
                  affilaction: 'serp',
                  revenue: $scope.tutor.public_price
                },
                products: [
                  {
                    id: $scope.tutor.id,
                    price: $scope.tutor.public_price,
                    brand: $scope.tutor.subjects,
                    category: $scope.tutor.markers,
                    quantity: 1
                  }
                ]
              }
            }
          });
        };
      }
    };
  });

}).call(this);

(function() {


}).call(this);

(function() {


}).call(this);

(function() {
  angular.module('Egerep').directive('subjectList', function() {
    return {
      restrict: 'E',
      scope: {
        subjects: '=',
        subjectIds: '=',
        "case": '@'
      },
      templateUrl: '/directives/subject-list',
      controller: function($scope, $element, $attrs, $rootScope) {
        $scope.byId = $attrs.byId !== void 0;
        if ($scope["case"] === void 0) {
          $scope["case"] = 'dative';
        }
        return $scope.findById = $rootScope.findById;
      }
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').directive('tutorName', function() {
    return {
      restrict: 'E',
      scope: {
        tutor: '='
      },
      templateUrl: '/directives/tutor-name'
    };
  });

}).call(this);

(function() {


}).call(this);

(function() {


}).call(this);

(function() {


}).call(this);

(function() {


}).call(this);

(function() {
  var apiPath, countable, updatable;

  angular.module('Egerep').factory('Tutor', function($resource) {
    return $resource(apiPath('tutors'), {
      id: '@id',
      type: '@type'
    }, {
      search: {
        method: 'POST',
        url: apiPath('tutors', 'search')
      },
      reviews: {
        method: 'GET',
        isArray: true,
        url: apiPath('tutors', 'reviews')
      },
      iteraction: {
        method: 'GET',
        url: "/api/tutors/iteraction/:id/:type"
      },
      login: {
        method: 'GET',
        url: apiPath('tutors', 'login')
      }
    });
  }).factory('Request', function($resource) {
    return $resource(apiPath('requests'), {
      id: '@id'
    }, updatable());
  }).factory('Sms', function($resource) {
    return $resource(apiPath('sms'), {
      id: '@id'
    }, updatable());
  }).factory('Cv', function($resource) {
    return $resource(apiPath('cv'), {
      id: '@id'
    });
  });

  apiPath = function(entity, additional) {
    if (additional == null) {
      additional = '';
    }
    return ("/api/" + entity + "/") + (additional ? additional + '/' : '') + ":id";
  };

  updatable = function() {
    return {
      update: {
        method: 'PUT'
      }
    };
  };

  countable = function() {
    return {
      count: {
        method: 'GET'
      }
    };
  };

}).call(this);

(function() {
  angular.module('Egerep').service('PhoneService', function() {
    var isFull;
    this.checkForm = function(element) {
      var phone_element, phone_number;
      phone_element = $(element).find('.phone-field');
      if (!isFull(phone_element.val())) {
        phone_element.focus().notify('номер телефона не заполнен полностью', notify_options);
        return false;
      }
      phone_number = phone_element.val().match(/\d/g).join('');
      if (phone_number[1] !== '4' && phone_number[1] !== '9') {
        phone_element.focus().notify('номер должен начинаться с 9 или 4', notify_options);
        return false;
      }
      return true;
    };
    isFull = function(number) {
      if (number === void 0 || number === "") {
        return false;
      }
      return !number.match(/_/);
    };
    return this;
  });

}).call(this);

(function() {
  angular.module('Egerep').service('RequestService', function(Request) {
    var trackDataLayer;
    this.request = function(tutor, element) {
      if (tutor.request === void 0) {
        tutor.request = {};
      }
      tutor.request.tutor_id = tutor.id;
      return Request.save(tutor.request, function() {
        tutor.request_sent = true;
        return trackDataLayer();
      }, function(response) {
        if (response.status === 422) {
          return angular.forEach(response.data, function(errors, field) {
            var selector;
            selector = "[ng-model$='" + field + "']";
            return $(element).find("input" + selector + ", textarea" + selector).focus().notify(errors[0], notify_options);
          });
        } else {
          return tutor.request_error = true;
        }
      });
    };
    trackDataLayer = function(tutor) {
      window.dataLayer = window.dataLayer || [];
      return window.dataLayer.push({
        event: 'purchase',
        ecommerce: {
          currencyCode: 'RUR',
          purchase: {
            actionField: {
              id: tutor.id,
              affilaction: 'serp',
              revenue: tutor.public_price
            },
            products: [
              {
                id: tutor.id,
                price: tutor.public_price,
                brand: tutor.subjects,
                category: tutor.markers,
                quantity: 1
              }
            ]
          }
        }
      });
    };
    return this;
  });

}).call(this);

(function() {
  angular.module('Egerep').service('SubjectService', function() {
    this.pairs = [[1, 2], [3, 4], [6, 7], [8, 9]];
    this.init = function(subjects) {
      return this.subjects = subjects;
    };
    this.pairsControl = function(subject_id) {
      if (subject_id) {
        return angular.forEach(this.subjects, (function(_this) {
          return function(enabled, id) {
            var pair;
            pair = _.filter(_this.pairs, function(p) {
              return p.indexOf(parseInt(subject_id)) !== -1;
            });
            if (!pair.length) {
              pair.push([subject_id]);
            }
            if (pair[0].indexOf(parseInt(id)) === -1) {
              return _this.subjects[id] = false;
            }
          };
        })(this));
      }
    };
    this.selected = function(subject_id) {
      return this.subjects !== void 0 && this.subjects[subject_id] !== void 0 && this.subjects[subject_id];
    };
    this.select = function(subject_id) {
      this.subjects[subject_id] = this.subjects[subject_id] ? !this.subjects[subject_id] : true;
      return this.pairsControl(subject_id);
    };
    this.getSelected = function() {
      var ids;
      ids = [];
      angular.forEach(this.subjects, function(enabled, id) {
        if (enabled) {
          return ids.push(id);
        }
      });
      return ids;
    };
    return this;
  });

}).call(this);

//# sourceMappingURL=app.js.map
