angular.module('Egerep').directive 'widgetLoadable', ($q, $timeout) ->
    restrict: 'A'
    scope:
        field: '='
    link: ($scope, $element, $attrs) ->
        q = $q.defer()

        $toggleBlock = $($element).children('.toggle-widget__inner')

        $($element).find('.widget-loadable__title').on('click').click ->
            text = $(@).find('span').text()
            if not q.promise.$$state.status
                $(@).find('span').html("<span class='loading loading-inside-widget'>загрузка</span>")
            else
                $(@).find('span').text(text)
            q.promise.then =>
                $(@).toggleClass('active').find('span').text(text)
                $toggleBlock.stop()
                $toggleBlock.slideToggle()

        $scope.$watch 'field', (newVal, oldVal) -> if newVal isnt undefined then q.resolve(true)
        # $scope.$watch 'field', (newVal, oldVal) ->
        #     if newVal isnt undefined then $timeout ->
        #         q.resolve(true)
        #     , 50000
