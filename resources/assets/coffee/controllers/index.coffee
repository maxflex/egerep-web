angular
    .module 'Egerep'
    .controller 'Index', ($scope, $timeout, $http, Tutor, StreamService) ->
        $timeout ->
            StreamService.run('landing', 'main')
        bindArguments($scope, arguments)
        $scope.selected_subject = '1'

        $scope.refreshSelect = ->
            $timeout ->
                $('.custom-select-sort').trigger('render')

        $scope.goSubject = (type) ->
            streamLink($scope.subject_routes[$scope.selected_subject], 'serp', type)

        # сотрудничает с 12 сентября 2000 года
        $scope.dateToText = (date) ->
            text_date = moment(date).format 'DD MMMM YYYY'
            # вырезаем дату, оставляем месяц и год
            # нужно именно так, чтобы осталось правильное склонение месяца
            text_date.substr(3)

        $scope.randomReview = ->
            $scope.loading_review = true
            $http.get 'api/reviews/random'
            .then (response) ->
                $scope.random_review = response.data
                $scope.loading_review = false
