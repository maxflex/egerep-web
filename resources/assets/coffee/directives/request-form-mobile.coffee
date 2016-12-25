angular.module('Egerep')
    .directive 'requestFormMobile', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
            index: '='
        templateUrl: 'directives/request-form-mobile'
        controller: ($scope, $element, $timeout, RequestService) ->
            $timeout ->
                if $scope.index isnt undefined
                    $scope.index++
                else
                    $scope.index = if window.location.hash then window.location.hash.substring(1) else null
            , 500
            $scope.request = -> RequestService.request($scope.tutor, $element, $scope.index, $scope.$parent.StreamService)
