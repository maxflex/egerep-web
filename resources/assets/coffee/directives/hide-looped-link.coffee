angular.module 'Egerep'
  .directive 'hideLoopedLink', ->
    restrict: 'A'
    link: ($scope, $element) ->
      anchor = $element
      unless $element.is 'a'
        anchor = $ 'a', $element
      $element.addClass 'ng-hide' if window.location.pathname is anchor.attr 'href'