angular.module 'Egerep'
    .directive 'subjectList', ->
        restrict: 'E'
        scope:
            subjects: '='
            subjectIds: '='
        templateUrl: '/directives/subject-list'
