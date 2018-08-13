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

//# sourceMappingURL=app.js.map
