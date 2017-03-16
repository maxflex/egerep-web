angular.module 'Egerep'
  .directive 'hideLoopedLink', ->
    restrict: 'A'
    link: ($scope, $element) ->
      anchor = $element
      anchor = $ 'a', $element unless $element.is 'a'
      if window.location.pathname is '/'
        anchor.removeAttr 'href'
      else
        $element.parent().remove() if window.location.pathname is anchor.attr 'href'