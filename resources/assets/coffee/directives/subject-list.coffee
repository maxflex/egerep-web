angular.module 'Egerep'
    .directive 'subjectList', ->
        restrict: 'E'
        scope:
            subjects: '='
            subjectIds: '='
            case: '@'
        templateUrl: '/directives/subject-list'
        controller: ($scope, $element, $attrs, $rootScope) ->
            $scope.byId = $attrs.byId isnt undefined
            $scope.case = 'dative' if $scope.case is undefined
            $scope.findById = $rootScope.findById
