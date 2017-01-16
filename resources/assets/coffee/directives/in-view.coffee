angular.module('Egerep').directive 'inView', ->
    restrict: 'A'
    scope:
        tutor: '=tutor'
        index: '=index'
    link: ($scope, $element, $attrs) ->
        # offset, чтобы было только при 100% видимости засчитывало
        # elementOffset.top += elementSize.height - 10
        $($element).on 'inview', (event, isInView) ->
            if isInView and $scope.$parent.viewed_tutors.indexOf($scope.tutor.id) is -1
                $scope.$parent.viewed_tutors.push($scope.tutor.id)
                $scope.$parent.StreamService.run 'view', $scope.$parent.StreamService.identifySource($scope.tutor),
                    tutor_id: $scope.tutor.id
                    position: $scope.index or null
                $($element).off('inview')
