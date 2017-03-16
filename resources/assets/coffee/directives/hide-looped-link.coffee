angular.module 'Egerep'
    .directive 'hideLoopedLink', ->
        restrict: 'A'
        link: ($scope, $element) ->
            if window.location.pathname is $element.attr('href')
                if window.location.pathname is '/'
                    $element.removeAttr('href')
                else
                    $element.parent().remove()
