angular.module 'Egerep'
    .directive 'tutorName', ->
        restrict: 'E'
        scope:
            tutor: '='
        templateUrl: '/directives/tutor-name'
