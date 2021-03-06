(function() {
  var indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  angular.module("Egerep", ['ngResource', 'angularFileUpload', 'angular-toArrayFilter', 'svgmap', 'ngSanitize', 'angucomplete-alt']).config([
    '$compileProvider', function($compileProvider) {
      return $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/);
    }
  ]).filter('cut', function() {
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
    $rootScope.withTailingDot = function(text) {
      var char;
      text = text.trim();
      char = text[text.length - 1];
      if (['!', '.'].indexOf(char) === -1) {
        text = text + '.';
      }
      return text;
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
    $rootScope.yearsPassed = function(year) {
      return moment().format("YYYY") - year;
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
    $rootScope.deny = function(ngModel, prop) {
      return ngModel[prop] = +(!ngModel[prop]);
    };
    $rootScope.closestMetro = function(markers) {
      var closest_metro;
      closest_metro = markers[0].metros[0];
      markers.forEach(function(marker) {
        return marker.metros.forEach(function(metro) {
          if (metro.meters < closest_metro.meters) {
            return closest_metro = metro;
          }
        });
      });
      return closest_metro.station.title;
    };
    $rootScope.closestMetros = function(markers) {
      var closest_metros;
      closest_metros = [];
      if (markers) {
        markers.forEach(function(marker, index) {
          closest_metros[index] = marker.metros[0];
          closest_metros[index].comment = marker.comment;
          return marker.metros.forEach(function(metro) {
            if (metro.meters < closest_metros[index].meters) {
              closest_metros[index] = metro;
              return closest_metros[index] = marker.comment;
            }
          });
        });
      }
      return closest_metros;
    };
    $rootScope.photoUrl = function(tutor) {
      if (tutor && tutor.photo_exists) {
        return "https://lk.ege-repetitor.ru/img/tutors/" + tutor.id + "." + tutor.photo_extension;
      } else {
        return "https://lk.ege-repetitor.ru/img/tutors/no-profile-img.gif";
      }
    };
    $rootScope.objectLength = function(obj) {
      return Object.keys(obj).length;
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
  angular.module('Egerep').controller('Cv', function($scope, $timeout, Tutor, FileUploader, Cv, PhoneService, StreamService) {
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
            $("input" + selector + ", textarea" + selector).focus();
            return $('html,body').animate({
              scrollTop: $("input" + selector + ", textarea" + selector).first().offset().top
            }, 0);
          });
        } else {
          return $scope.application.error = true;
        }
      });
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').controller('Empty', function($scope, StreamService) {
    return bindArguments($scope, arguments);
  });

}).call(this);

(function() {
  angular.module('Egerep').controller('Index', function($scope, $timeout, $http, Tutor, StreamService) {
    var searchReviews;
    $timeout(function() {
      StreamService.run('landing', 'main');
      $scope.has_more_reviews = true;
      $scope.reviews_page = 0;
      $scope.reviews = [];
      return searchReviews();
    });
    $scope.nextReviewsPage = function() {
      $scope.reviews_page++;
      return searchReviews();
    };
    $scope.roundRating = function(review) {
      return Math.round(review.score / 2);
    };
    searchReviews = function() {
      $scope.searching_reviews = true;
      return $http.get('/api/reviews?page=' + $scope.reviews_page).then(function(response) {
        $scope.searching_reviews = false;
        $scope.reviews = $scope.reviews.concat(response.data.reviews);
        return $scope.has_more_reviews = response.data.has_more_reviews;
      });
    };
    bindArguments($scope, arguments);
    $scope.selected_subject = '1';
    $scope.refreshSelect = function() {
      return $timeout(function() {
        return $('.custom-select-sort').trigger('render');
      });
    };
    $scope.goSubject = function(where) {
      return streamLink($scope.subject_routes[$scope.selected_subject], 'serp_' + where, $scope.findById($scope.subjects, $scope.selected_subject).eng);
    };
    $scope.onWebsite = function(tutor, type) {
      var attachment_month, attachment_year, current_month, current_year, month_diff, year_diff;
      if (type == null) {
        type = 'month';
      }
      if (!tutor) {
        return;
      }
      current_year = parseInt(moment().format('YYYY'));
      attachment_year = parseInt(moment(tutor.created_at).format('YYYY'));
      current_month = parseInt(moment().format('M'));
      attachment_month = parseInt(moment(tutor.created_at).format('M'));
      month_diff = current_month - attachment_month;
      year_diff = current_year - attachment_year;
      if (month_diff < 0) {
        month_diff = 12 + month_diff;
        year_diff--;
      }
      if (type === 'month') {
        return month_diff;
      } else {
        return year_diff;
      }
    };
    return $scope.randomReview = function() {
      $scope.loading_review = true;
      return $http.get('api/reviews/random').then(function(response) {
        $scope.random_review = response.data;
        return $scope.loading_review = false;
      });
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').controller('LoginCtrl', function($scope, $timeout, Sms, Tutor, StreamService) {
    var login;
    bindArguments($scope, arguments);
    $scope.loading = true;
    login = function() {
      return Tutor.login({}, function(response) {
        $scope.tutor = response;
        return redirect('/payment');
      }, function() {
        $scope.tutor = null;
        return $scope.loading = false;
      });
    };
    login();
    $scope.sendCode = function() {
      $scope.error_message = false;
      $scope.loading = true;
      return Sms.save({
        phone: $scope.phone
      }, function() {
        $scope.code_sent = true;
        $timeout(function() {
          return $('#code-input').focus();
        });
        return $scope.loading = false;
      }, function() {
        $scope.error_message = 'неверный номер телефона';
        return $scope.loading = false;
      });
    };
    return $scope.checkCode = function() {
      $scope.error_message = false;
      $scope.loading = true;
      return Sms.get({
        code: $scope.code
      }, function() {
        return login();
      }, function(response) {
        if (response.status === 403) {
          redirect('/');
        }
        $scope.error_message = 'код введен неверно';
        return $scope.loading = false;
      });
    };
  });

}).call(this);

(function() {
  angular.module('Egerep').controller('Payments', function($scope, $timeout, $http, StreamService) {
    bindArguments($scope, arguments);
    $scope.loading = false;
    $scope.initial_loading = true;
    $scope.sum = '';
    $scope.fio = '';
    $scope.email = '';
    $scope.error = '';
    $scope.proceed = function() {
      $scope.error = '';
      $scope.loading = true;
      return $http.post('api/payments', {
        sum: $scope.sum,
        fio: $scope.fio,
        email: $scope.email
      }).then(function(r) {
        return redirect(r.data.formUrl);
      }, function(e) {
        $scope.error = e.data[Object.keys(e.data)[0]][0];
        return $scope.loading = false;
      });
    };
    return $timeout(function() {
      if ($scope.orderId) {
        return $http.post('api/payments/getOrderStatus', {
          orderId: $scope.orderId,
          lang: $scope.lang
        }).then(function(r) {
          $scope.orderStatus = r.data;
          return $scope.initial_loading = false;
        });
      } else {
        return $scope.initial_loading = false;
      }
    });
  });

}).call(this);

(function() {
  angular.module('Egerep').constant('REVIEWS_PER_PAGE', 5).controller('Tutors', function($scope, $http, $timeout, Tutor, SubjectService, REVIEWS_PER_PAGE, Genders, Request, StreamService, Sources) {
    var bindWatchers, filter, filter_used, handleScrollDesktop, handleScrollMobile, highlight, search, search_count;
    bindArguments($scope, arguments);
    search_count = 0;
    $scope.popups = {};
    $scope.station_ids = {};
    $scope.paramsCount = function() {
      var count;
      count = 0;
      if ($scope.search.hasOwnProperty('subjects') && Object.keys($scope.search.subjects).length) {
        count++;
      }
      if ($scope.search.hasOwnProperty('sort') && $scope.search.sort) {
        count++;
      }
      if ($scope.search.hasOwnProperty('place') && $scope.search.place) {
        count++;
      }
      return count;
    };
    $scope.openTutor = function(tutor, index) {
      var data;
      data = {
        event: 'configuration',
        eventCategory: 'full-tutor-profile'
      };
      dataLayerPush(data);
      console.log(data);
      $scope.popup_tutor = tutor;
      $scope.gmap(tutor, index);
      return openTutor();
    };
    $scope.filterPopup = function(popup) {
      if (Object.keys($scope.popups).length) {
        $scope.popups = {};
        return;
      }
      $scope.popups[popup] = true;
      if ($scope.mobile) {
        openModal("filter-" + popup);
      }
      StreamService.run('filter_open', popup);
      if (popup === 'all') {
        if ($scope.show_intro) {
          return $scope.anno.hide();
        }
      }
    };
    $scope.getStarRating = function(rating) {
      var segment;
      segment = (Math.floor(rating / 2) * 2) + 1;
      return (segment - ((segment - rating) * 0.6)) * 10;
    };
    $scope.getIndex = function(index) {
      if (index == null) {
        index = null;
      }
      if (index !== null) {
        return parseInt(index) + 1;
      }
      return $scope.index_from_hash || null;
    };
    $scope.streamLink = streamLink;
    $scope.profileLink = function(tutor, index, async, event_name) {
      var link;
      if (async == null) {
        async = true;
      }
      if (event_name == null) {
        event_name = 'tutor_profile';
      }
      index = $scope.getIndex(index);
      link = "" + tutor.id;
      if (index) {
        link += "#" + index;
      }
      if (async) {
        window.open(link, '_blank');
      }
      return StreamService.run('go_' + event_name, StreamService.identifySource(tutor), {
        position: index,
        tutor_id: tutor.id
      }).then(function() {
        if (!async) {
          return window.location = link;
        }
      });
    };
    $scope.profilePage = function() {
      return RegExp(/^\/[\d]+$/).test(window.location.pathname);
    };
    bindWatchers = function() {
      $scope.$watchCollection('search.subjects', function(newVal, oldVal) {
        var data;
        if (newVal === oldVal) {
          return;
        }
        data = {
          event: 'configuration',
          eventCategory: 'subjects',
          eventAction: $scope.SubjectService.getSelected().map(function(subject_id) {
            return $scope.subjects[subject_id].eng;
          }).join(',')
        };
        dataLayerPush(data);
        return console.log(data);
      });
      $scope.$watchCollection('search.place', function(newVal, oldVal) {
        var data;
        if (newVal === oldVal) {
          return;
        }
        data = {
          event: 'configuration',
          eventCategory: 'place',
          eventAction: $scope.search.place
        };
        dataLayerPush(data);
        return console.log(data);
      });
      $scope.$watchCollection('search.sort', function(newVal, oldVal) {
        var data;
        if (newVal === oldVal) {
          return;
        }
        data = {
          event: 'configuration',
          eventCategory: 'sort',
          eventAction: $scope.search.sort
        };
        dataLayerPush(data);
        return console.log(data);
      });
      return $scope.$watchCollection('search.station_id', function(newVal, oldVal) {
        var data;
        if (newVal === oldVal) {
          return;
        }
        $scope.search.sort = newVal ? 'nearest-metro' : 'most-popular';
        data = {
          event: 'configuration',
          eventCategory: 'station',
          eventAction: $scope.search.station_id ? $scope.stations[$scope.search.station_id].title : ''
        };
        dataLayerPush(data);
        return console.log(data);
      });
    };
    handleScrollDesktop = function() {
      var sticky, wrapper;
      wrapper = $('.filter-groups');
      sticky = wrapper.position().top - 1;
      return $(window).on('scroll', function() {
        if (window.pageYOffset > sticky) {
          if (!$('body').hasClass('sticky')) {
            return $('body').addClass('sticky');
          }
        } else {
          if ($('body').hasClass('sticky')) {
            return $('body').removeClass('sticky');
          }
        }
      });
    };
    handleScrollMobile = function() {};
    $timeout(function() {
      $timeout(bindWatchers, 500);
      if (!$scope.profilePage() && window.location.pathname !== '/request') {
        if ($scope.selected_subjects) {
          $scope.selected_subjects.split(',').forEach(function(subject_id) {
            return $scope.search.subjects[subject_id] = true;
          });
        }
        SubjectService.init($scope.search.subjects);
        StreamService.run('landing', 'serp');
        return $scope.filter();
      } else {
        $scope.gmap($scope.popup_tutor);
        $scope.index_from_hash = window.location.hash.substring(1);
        return StreamService.run('landing', StreamService.identifySource(), $scope.profilePage() ? {
          tutor_id: $scope.tutor.id,
          position: $scope.getIndex()
        } : {});
      }
    });
    $scope.pairs = [[1, 2], [3, 4], [6, 7], [8, 9]];
    $scope.viewed_tutors = [];
    $scope.dateToText = function(date) {
      var text_date;
      text_date = moment(date).format('DD MMMM YYYY');
      return text_date.substr(3);
    };
    $scope.onWebsite = function(tutor, type) {
      var attachment_month, attachment_year, current_month, current_year, month_diff, year_diff;
      if (type == null) {
        type = 'month';
      }
      if (!tutor) {
        return;
      }
      current_year = parseInt(moment().format('YYYY'));
      attachment_year = parseInt(moment(tutor.created_at).format('YYYY'));
      current_month = parseInt(moment().format('M'));
      attachment_month = parseInt(moment(tutor.created_at).format('M'));
      month_diff = current_month - attachment_month;
      year_diff = current_year - attachment_year;
      if (month_diff < 0) {
        month_diff = 12 + month_diff;
        year_diff--;
      }
      if (type === 'month') {
        return month_diff;
      } else {
        return year_diff;
      }
    };
    $scope.requestSent = function(tutor) {
      return tutor.request_sent || $scope.sent_ids.indexOf(tutor.id) !== -1;
    };
    $scope.gmap = function(tutor, index) {
      tutor.map_initialized = true;
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
          var marker_location, new_marker;
          marker_location = new google.maps.LatLng(marker.lat, marker.lng);
          bounds.extend(marker_location);
          return new_marker = newMarker(marker_location, map);
        });
        if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
          extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.005, bounds.getNorthEast().lng() + 0.005);
          extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.005, bounds.getNorthEast().lng() - 0.005);
          bounds.extend(extendPoint1);
          bounds.extend(extendPoint2);
        }
        map.fitBounds(bounds);
        map.panToBounds(bounds);
        map.setZoom(tutor.markers.length > 1 ? 9 : 14);
        return google.maps.event.addListenerOnce(map, 'idle', function() {
          return $('div:has(>a[href^="https://www.google.com/maps"])').remove();
        });
      });
      return $scope.toggleShow(tutor, 'map_shown', 'google_map', index);
    };
    $scope.getMetros = function(tutor) {
      return _.chain(tutor.markers).pluck('metros').flatten().value();
    };
    $scope.loadReviews = function(tutor) {
      if (tutor.all_reviews === void 0) {
        tutor.reviews_loading = true;
        return tutor.all_reviews = Tutor.reviews({
          id: tutor.id
        }, function(response) {
          tutor.reviews_loading = false;
          return $scope.showMoreReviews(tutor);
        });
      }
    };
    $scope.reviews = function(tutor, index) {
      StreamService.run('reviews', StreamService.identifySource(tutor), {
        position: $scope.getIndex(index),
        tutor_id: tutor.id
      });
      if (tutor.all_reviews === void 0) {
        tutor.all_reviews = Tutor.reviews({
          id: tutor.id
        }, function(response) {
          return $scope.showMoreReviews(tutor);
        });
      }
      return $scope.toggleShow(tutor, 'show_reviews', 'reviews', false);
    };
    $scope.showMoreReviews = function(tutor, index) {
      var from, to;
      if (tutor.all_reviews === void 0) {
        $scope.loadReviews(tutor);
        return;
      }
      if (tutor.reviews_page) {
        StreamService.run('reviews_more', StreamService.identifySource(tutor), {
          position: $scope.getIndex(index),
          tutor_id: tutor.id,
          depth: (tutor.reviews_page + 1) * REVIEWS_PER_PAGE + (tutor.reviews_page === 1 ? 2 : 0)
        });
      }
      tutor.reviews_page = !tutor.reviews_page ? 1 : tutor.reviews_page + 1;
      from = (tutor.reviews_page - 1) * REVIEWS_PER_PAGE + 2;
      to = from + REVIEWS_PER_PAGE;
      tutor.displayed_reviews = tutor.all_reviews.slice(0, to);
      return highlight('search-result-reviews-text');
    };
    $scope.reviewsLeft = function(tutor) {
      var reviews_left;
      reviews_left = tutor.reviews_count - tutor.displayed_reviews.length;
      if (reviews_left > REVIEWS_PER_PAGE) {
        return REVIEWS_PER_PAGE;
      } else {
        return reviews_left;
      }
    };
    filter_used = false;
    $scope.filter = function(type) {
      var params;
      if (type == null) {
        type = null;
      }
      $('html').scrollTop(0);
      $scope.popups = {};
      closeModal();
      $scope.tutors = [];
      $scope.page = 1;
      if (filter_used) {
        StreamService.updateCookie({
          search: StreamService.cookie.search + 1
        });
        if (true) {
          params = {
            search: StreamService.cookie.search,
            subjects: $scope.SubjectService.getSelected().join(','),
            station_id: $scope.search.station_id,
            sort: $scope.search.sort,
            place: $scope.search.place
          };
        } else {
          params = {
            search: StreamService.cookie.search,
            subjects: $scope.SubjectService.getSelected().join(','),
            station_id: $scope.search.station_id,
            priority: $scope.search.priority
          };
        }
        return StreamService.run('filter', type, params).then(function() {
          return filter();
        });
      } else {
        filter();
        return filter_used = true;
      }
    };
    filter = function() {
      search();
      if ($scope.search.hidden_filter && search_count) {
        delete $scope.search.hidden_filter;
      }
      return $.cookie('search', JSON.stringify($scope.search));
    };
    $scope.nextPage = function() {
      $scope.page++;
      StreamService.run('load_more_tutors', null, {
        page: $scope.page
      });
      return search();
    };
    $scope.$watch('page', function(newVal, oldVal) {
      if (newVal !== void 0) {
        return $.cookie('page', $scope.page);
      }
    });
    $scope.isLastPage = function() {
      if (!$scope.data) {
        return;
      }
      return $scope.data.current_page >= $scope.data.last_page;
    };
    $scope.unselectSubjects = function(subject_id) {
      return angular.forEach($scope.search.subjects, function(enabled, id) {
        var pair;
        if (subject_id) {
          pair = _.filter(scope.pairs, function(p) {
            return p.indexOf(parseInt(subject_id)) !== -1;
          });
          if (!pair.length) {
            pair.push([subject_id]);
          }
          if (pair[0].indexOf(parseInt(id)) === -1) {
            return $scope.search.subjects[id] = false;
          }
        } else {
          return $scope.search.subjects[id] = false;
        }
      });
    };
    search = function() {
      $scope.searching = true;
      return Tutor.search({
        filter_used: filter_used,
        tutor_id: getUrlParam('tutor_id'),
        page: $scope.page,
        search: $scope.search
      }, function(response) {
        search_count++;
        $scope.searching = false;
        if (search_count === 1) {
          $timeout(function() {
            if ($scope.mobile) {
              handleScrollMobile();
            } else {
              handleScrollDesktop();
            }
            if ($scope.show_intro) {
              if ($scope.mobile) {
                $scope.anno = new Anno({
                  target: '.filter-fixed:first',
                  content: '<h4>Пользуйтесь фильтрами</h4>В базе ' + $scope.tutors_count + ' репетиторов. Чтобы увидеть анкеты подходящие именно вам, пользуйтесь фильтрами',
                  position: 'center-top',
                  arrowPosition: 'center-bottom',
                  buttons: [AnnoButton.DoneButton],
                  onHide: function() {
                    return $('body').removeClass('disable-scroll');
                  }
                });
                $timeout(function() {
                  return $scope.anno.show();
                }, 100);
                return $('body').addClass('disable-scroll');
              } else {
                $scope.anno = new Anno({
                  target: '.filter-groups:first',
                  content: '<h4>Пользуйтесь фильтрами</h4>В базе ' + $scope.tutors_count + ' репетиторов. Чтобы увидеть анкеты подходящие именно вам, пользуйтесь фильтрами',
                  position: 'center-bottom',
                  arrowPosition: 'center-top',
                  buttons: [AnnoButton.DoneButton]
                });
                $('.fake-select').on('click', function() {
                  return $scope.anno.hide();
                });
                return $scope.anno.show();
              }
            }
          }, 500);
        }
        if (response.hasOwnProperty('url')) {
          console.log('redirectring...');
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
            $timeout(function() {
              return bindToggle();
            });
          }
          return $timeout(function() {
            return window.dispatchEvent(new Event('scroll'));
          });
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
    $scope.showSvg = function(tutor, index) {
      return $scope.toggleShow(tutor, 'show_svg', 'metro_map', index);
    };
    $scope.toggleShow = function(tutor, prop, iteraction_type, index) {
      if (index == null) {
        index = null;
      }
      if (tutor[prop]) {
        return $timeout(function() {
          return tutor[prop] = false;
        }, $scope.mobile ? 400 : 0);
      } else {
        tutor[prop] = true;
        if (index !== false) {
          return StreamService.run(iteraction_type, StreamService.identifySource(tutor), {
            position: $scope.getIndex(index),
            tutor_id: tutor.id
          });
        }
      }
    };
    $scope.shortenGrades = function(tutor) {
      var a, combo_end, combo_start, i, j, limit, pairs;
      a = tutor.grades;
      if (a.length < 1) {
        return;
      }
      limit = a.length - 1;
      combo_end = -1;
      pairs = [];
      i = 0;
      while (i <= limit) {
        combo_start = parseInt(a[i]);
        if (combo_start > 11) {
          i++;
          combo_end = -1;
          pairs.push($scope.grades[combo_start].title);
          continue;
        }
        if (combo_start <= combo_end) {
          i++;
          continue;
        }
        j = i;
        while (j <= limit) {
          combo_end = parseInt(a[j]);
          if (combo_end >= 11) {
            break;
          }
          if (parseInt(a[j + 1]) - combo_end > 1) {
            break;
          }
          j++;
        }
        if (combo_start !== combo_end) {
          pairs.push(combo_start + '–' + combo_end + ' классы');
        } else {
          pairs.push(combo_start + ' класс');
        }
        i++;
      }
      return pairs.join(', ');
    };
    $scope.roundRating = function(review) {
      return Math.round(review.score / 2);
    };
    $scope.popup = function(id, tutor, fn, index) {
      if (tutor == null) {
        tutor = null;
      }
      if (fn == null) {
        fn = null;
      }
      if (index == null) {
        index = null;
      }
      openModal(id);
      if (tutor !== null) {
        $scope.popup_tutor = tutor;
      }
      if (fn !== null) {
        $timeout(function() {
          return $scope[fn](tutor, index);
        });
      }
      return $scope.index = $scope.getIndex(index);
    };
    $scope.request = function(tutor, index) {
      return StreamService.run('contact_tutor_button', StreamService.identifySource(tutor), {
        position: $scope.getIndex(index),
        tutor_id: tutor.id
      });
    };
    $scope.expandMoreInfo = function(tutor, index) {
      tutor.expand_info = true;
      return StreamService.run('expand_more_tutor_info', StreamService.identifySource(tutor), {
        position: $scope.getIndex(index),
        tutor_id: tutor.id
      });
    };
    $scope.expand = function(tutor, index) {
      var event_name;
      tutor.is_expanded = !tutor.is_expanded;
      if (!tutor.map_initialized) {
        $scope.gmap(tutor, index);
      }
      event_name = tutor.is_expanded ? 'expand_tutor_info' : 'shrink_tutor_info';
      return StreamService.run(event_name, StreamService.identifySource(tutor), {
        position: $scope.getIndex(index),
        tutor_id: tutor.id
      });
    };
    $scope.tutorPopup = function(tutor, index) {
      $('#modal-tutor .modal-content').scrollTop(0);
      return $scope.gmap(tutor, index);
    };
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
    $scope.hasSelectedStation = function(tutor) {
      if (!$scope.search || $scope.search.sort !== 5) {
        return false;
      }
      return tutor.svg_map.indexOf(parseInt($scope.search.station_id)) !== -1;
    };
    $scope.departsEverywhere = function(tutor) {
      if (!tutor.svg_map) {
        return false;
      }
      if (typeof tutor.svg_map === 'string') {
        tutor.svg_map = tutor.svg_map.split(',');
      }
      return tutor.svg_map.length >= 214;
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
  angular.module('Egerep').value('Genders', {
    male: 'мужской',
    female: 'женский'
  }).value('Sources', {
    LANDING: 'landing',
    LANDING_PROFILE: 'landing_profile',
    LANDING_HELP: 'landing_help',
    FILTER: 'filter',
    PROFILE_REQUEST: 'profilerequest',
    SERP_REQUEST: 'serprequest',
    HELP_REQUEST: 'helprequest',
    MORE_TUTORS: 'more_tutors'
  });

}).call(this);

(function() {
  angular.module('Egerep').directive('ngAge', function() {
    return {
      restrict: 'A',
      require: 'ngModel',
      scope: {
        ngModel: '=',
        prefix: '@'
      },
      link: function(scope, element, attrs, ngModel) {
        var updateModel;
        updateModel = function(value) {
          ngModel.$setViewValue(value);
          return ngModel.$render();
        };
        return $(element).on('blur', function() {
          var age_from, age_to;
          $('.age-field').removeClass('has-error');
          age_to = scope.$parent.search.age_to;
          age_from = scope.$parent.search.age_from;
          if (age_to && age_from) {
            if (getNumber(age_from) > getNumber(age_to)) {
              $('.age-field').addClass('has-error');
            }
          }
          if (scope.ngModel) {
            return updateModel(scope.prefix + ' ' + scope.ngModel + ' лет');
          }
        }).on('keyup', function() {
          return updateModel(getNumber(scope.ngModel));
        }).on('focus', function() {
          return updateModel(getNumber(scope.ngModel));
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
          if (!errors) {
            return;
          }
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
  angular.module('Egerep').directive('hideLoopedLink', function() {
    return {
      restrict: 'A',
      link: function($scope, $element) {
        if (window.location.pathname === $element.attr('href')) {
          if (window.location.pathname === '/') {
            return $element.removeAttr('href');
          } else {
            return $element.parent().remove();
          }
        }
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
  angular.module('Egerep').directive('inView', function() {
    return {
      restrict: 'A',
      scope: {
        tutor: '=tutor',
        index: '=index'
      },
      link: function($scope, $element, $attrs) {
        return $($element).on('inview', function(event, isInView) {
          if (isInView && $scope.$parent.viewed_tutors.indexOf($scope.tutor.id) === -1) {
            $scope.$parent.viewed_tutors.push($scope.tutor.id);
            $scope.$parent.StreamService.run('view', $scope.$parent.StreamService.identifySource($scope.tutor), {
              tutor_id: $scope.tutor.id,
              position: $scope.index || null
            });
            return $($element).off('inview');
          }
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
        return $(element).inputmask("+7 (999) 999-99-99", {
          autoclear: false,
          showMaskOnHover: false
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
          'month': ['месяц', 'месяца', 'месяцев'],
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
          'marks': ['оценка', 'оценки', 'оценок'],
          'review': ['отзыв', 'отзыва', 'отзывов'],
          'request': ['заявка', 'заявки', 'заявок'],
          'station': ['станцию', 'станции', 'станций'],
          'tutor': ['репетитор', 'репетитора', 'репетиторов'],
          'profile': ['анкета', 'анкеты', 'анкет'],
          'schooler': ['школьник нашел', 'школьника нашли', 'школьников нашли'],
          'taught': ['подготовлен', 'подготовлено', 'подготовлено'],
          'address': ['адрес', 'адреса', 'адресов']
        };
      }
    };
  });

}).call(this);

(function() {


}).call(this);

(function() {
  angular.module('Egerep').directive('requestForm', function() {
    return {
      replace: true,
      scope: {
        tutor: '=',
        sentIds: '=',
        index: '=',
        source: '@'
      },
      templateUrl: function(elem, attrs) {
        if (attrs.hasOwnProperty('mobile')) {
          return 'directives/request-form-mobile';
        } else {
          return 'directives/request-form';
        }
      },
      controller: function($scope, $element, $attrs, $timeout, $rootScope, Request, Sources) {
        var trackDataLayer;
        $scope.fixedHeight = $attrs.hasOwnProperty('fixedHeight');
        $scope.request = function() {
          if (!$scope.tutor) {
            $scope.tutor = {};
          }
          if ($scope.tutor.request === void 0) {
            $scope.tutor.request = {};
          }
          $scope.tutor.request.tutor_id = $scope.tutor.id;
          return Request.save($scope.tutor.request, function() {
            $scope.tutor.request_sent = true;
            $scope.$parent.StreamService.run('request', $scope.source || $scope.$parent.StreamService.identifySource($scope.tutor), {
              position: $scope.index || $scope.$parent.index,
              tutor_id: $scope.tutor.id
            });
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
          return dataLayerPush({
            event: 'purchase',
            ecommerce: {
              currencyCode: 'RUB',
              purchase: {
                actionField: {
                  id: googleClientId(),
                  affiliation: $scope.$parent.StreamService.identifySource(),
                  revenue: $scope.tutor.public_price
                },
                products: [
                  {
                    id: $scope.tutor.id,
                    price: $scope.tutor.public_price,
                    brand: $scope.tutor.subjects ? $scope.tutor.subjects.join(',') : null,
                    category: $scope.tutor.gender + '_' + $scope.tutor.age,
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
  angular.module('Egerep').directive('widgetLoadable', function($q, $timeout) {
    return {
      restrict: 'A',
      scope: {
        field: '='
      },
      link: function($scope, $element, $attrs) {
        var $toggleBlock, q;
        q = $q.defer();
        $toggleBlock = $($element).children('.toggle-widget__inner');
        $($element).find('.widget-loadable__title').on('click').click(function() {
          var text;
          text = $(this).find('span').text();
          if (!q.promise.$$state.status) {
            $(this).find('span').html("<span class='loading loading-inside-widget'>загрузка</span>");
          } else {
            $(this).find('span').text(text);
          }
          return q.promise.then((function(_this) {
            return function() {
              $(_this).parent().toggleClass('arrow-active');
              $(_this).find('span').text(text);
              $toggleBlock.stop();
              return $toggleBlock.slideToggle();
            };
          })(this));
        });
        return $scope.$watch('field', function(newVal, oldVal) {
          if (newVal !== void 0) {
            return q.resolve(true);
          }
        });
      }
    };
  });

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
  }).factory('Stream', function($resource) {
    return $resource(apiPath('stream'), {
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
  angular.module('Egerep').service('StreamService', function($http, $timeout, Stream, SubjectService, Sources) {
    this.identifySource = function(tutor) {
      if (tutor == null) {
        tutor = void 0;
      }
      if (tutor !== void 0 && tutor.is_similar) {
        return 'similar';
      }
      if (RegExp(/^\/[\d]+$/).test(window.location.pathname)) {
        return 'tutor';
      }
      if (window.location.pathname === '/request') {
        return 'help';
      }
      if (window.location.pathname === '/') {
        return 'main';
      }
      return 'serp';
    };
    this.generateEventString = function(params) {
      var parts, search;
      search = $.cookie('search');
      if (search !== void 0) {
        $.each(JSON.parse(search), function(key, value) {
          if (!params.hasOwnProperty(key)) {
            return params[key] = value;
          }
        });
      }
      parts = [];
      $.each(params, function(key, value) {
        if ((key === 'action' || key === 'type' || key === 'google_id' || key === 'yandex_id' || key === 'id' || key === 'hidden_filter' || key === 'sort' || key === 'place' || key === 'gender') || !value) {
          return;
        }
        switch (key) {
          case 'priority':
            switch (parseInt(value)) {
              case 2:
                value = 'tutor';
                break;
              case 3:
                value = 'client';
                break;
              case 4:
                value = 'maxprice';
                break;
              case 5:
                value = 'minprice';
                break;
              case 6:
                value = 'rating';
                break;
              default:
                value = 'pop';
            }
            break;
          case 'subjects':
            if (typeof value === "object") {
              value = SubjectService.getSelected(value).join(',');
            }
        }
        return parts.push(key + '=' + value);
      });
      return parts.join('_');
    };
    this.updateCookie = function(params) {
      if (this.cookie === void 0) {
        this.cookie = {};
      }
      $.each(params, (function(_this) {
        return function(key, value) {
          return _this.cookie[key] = value;
        };
      })(this));
      return $.cookie('stream', JSON.stringify(this.cookie), {
        expires: 365,
        path: '/'
      });
    };
    this.initCookie = function() {
      if ($.cookie('stream') !== void 0) {
        return this.cookie = JSON.parse($.cookie('stream'));
      } else {
        return this.updateCookie({
          step: 0,
          search: 0
        });
      }
    };
    this.run = function(action, type, additional) {
      if (additional == null) {
        additional = {};
      }
      if (this.cookie === void 0) {
        this.initCookie();
      }
      if (!this.initialized) {
        return $timeout((function(_this) {
          return function() {
            return _this._run(action, type, additional);
          };
        })(this), 1000);
      } else {
        return this._run(action, type, additional);
      }
    };
    this._run = function(action, type, additional) {
      var params;
      if (additional == null) {
        additional = {};
      }
      this.updateCookie({
        step: this.cookie.step + 1
      });
      params = {
        action: action,
        type: type,
        step: this.cookie.step,
        google_id: googleClientId(),
        yandex_id: yaCounter1411783 ? yaCounter1411783.getClientID() : '',
        mobile: typeof isMobile === 'undefined' ? '0' : '1'
      };
      $.each(additional, (function(_this) {
        return function(key, value) {
          return params[key] = value;
        };
      })(this));
      console.log(action, type, params);
      if (action !== 'view') {
        console.log(this.generateEventString(angular.copy(params)));
      }
      if (action !== 'view') {
        dataLayerPush({
          event: 'configuration',
          eventCategory: ("action=" + action) + (type ? "_type=" + type : ""),
          eventAction: this.generateEventString(angular.copy(params))
        });
      }
      return Stream.save(params).$promise;
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
    this.getSelected = function(subjects) {
      var ids;
      if (subjects == null) {
        subjects = null;
      }
      ids = [];
      angular.forEach(subjects || this.subjects, function(enabled, id) {
        if (enabled) {
          return ids.push(id);
        }
      });
      return ids;
    };
    this.opacityControl = function(id) {
      var pair, selected_id;
      if (!this.getSelected().length) {
        return false;
      }
      selected_id = parseInt(this.getSelected()[0]);
      pair = _.filter(this.pairs, function(p) {
        return p.indexOf(selected_id) !== -1;
      });
      if (!pair.length) {
        return selected_id !== id;
      } else {
        return pair[0].indexOf(parseInt(id)) === -1;
      }
    };
    return this;
  });

}).call(this);

//# sourceMappingURL=app.js.map
