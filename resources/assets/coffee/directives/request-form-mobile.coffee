angular.module('Egerep')
    .directive 'requestFormMobile', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
            index: '='
        templateUrl: 'directives/request-form-mobile'
        controller: ($scope, $element, $timeout, RequestService) ->
            $scope.request = -> RequestService.request($scope.tutor, $element, $scope.index, $scope.$parent.StreamService)
