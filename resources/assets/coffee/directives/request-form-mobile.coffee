angular.module('Egerep')
    .directive 'requestFormMobile', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
        templateUrl: 'directives/request-form-mobile'
        controller: ($scope, $element, $timeout, Request, RequestService) ->
            $scope.request = -> RequestService.request($scope.tutor, $element)
