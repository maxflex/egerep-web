# angular.module('Egerep').directive 'metroList', ->
#     restrict: 'E'
#     templateUrl: 'directives/metro-list'
#     scope:
#         markers: '='
#     controller: ($scope, $element, $attrs) ->
#         $scope.inline = $attrs.hasOwnProperty('inline')
#         $scope.one_station = $attrs.hasOwnProperty('oneStation')
#
#         $scope.short = (title) ->
#             title.slice(0,3).toUpperCase()
#
#         $scope.minutes = (minutes) ->
#             Math.round minutes
