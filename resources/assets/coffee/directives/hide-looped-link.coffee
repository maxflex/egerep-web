angular.module 'Egerep'
  .directive 'hideLoopedLink', ->
    restrict: 'A'
    link: ($scope, $element) ->
      anchor = $element
      anchor = $ 'a', $element unless $element.is 'a'
      $element.addClass 'ng-hide' if window.location.pathname is anchor.attr 'href'