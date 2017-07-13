angular.module('Egerep').directive 'errors', ->
    restrict: 'E'
    templateUrl: 'directives/errors'
    scope:
        model: '@'
    controller: ($scope, $element, $attrs) ->
        $scope.only_first = $attrs.hasOwnProperty('onlyFirst')

        $scope.getErrors = ->
            return if $scope.$parent.errors is undefined
            errors = $scope.$parent.errors[$scope.model]
            return if not errors
            if $scope.only_first then [errors[0]] else errors
