# angular.module 'Egecms'
#     .directive 'ngSelect', ->
#         restrict: 'E'
#         replace: true
#         scope:
#             object: '='
#             model: '='
#             noneText: '@'
#             label: '@'
#         templateUrl: 'directives/ngselect'
#         controller: ($scope, $element, $attrs, $timeout) ->
#             # выбираем первое значение по умолчанию, если нет noneText
#             if not $scope.noneText
#                 $scope.model = _.first Object.keys($scope.object)
#
#             $timeout ->
#                 $($element).selectpicker()
#             , 100
