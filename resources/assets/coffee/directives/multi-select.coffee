# angular.module 'Egecms'
#     .directive 'ngMulti', ->
#         restrict: 'E'
#         replace: true
#         scope:
#             object: '='
#             model: '='
#             label: '@'
#             noneText: '@'
#         templateUrl: 'directives/ngmulti'
#         controller: ($scope, $element, $attrs, $timeout) ->
#             $timeout ->
#                 $($element).selectpicker
#                     noneSelectedText: $scope.noneText
#             , 100
