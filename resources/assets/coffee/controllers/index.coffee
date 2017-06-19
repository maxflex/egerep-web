angular
    .module 'Egerep'
    .controller 'Index', ($scope, $timeout, $http, Tutor, StreamService) ->
        $timeout ->
            StreamService.run('landing', 'main')
            $scope.has_more_reviews = true
            $scope.reviews_page = 0
            $scope.reviews = []
            searchReviews()

        $scope.nextReviewsPage = ->
            $scope.reviews_page++
            # StreamService.run('load_more_tutors', null, {page: $scope.page})
            searchReviews()

        # $scope.$watch 'page', (newVal, oldVal) -> $.cookie('page', $scope.page) if newVal isnt undefined

        searchReviews = (first_load = false) ->
            $scope.searching_reviews = true
            $http.get('/api/reviews?page=' + $scope.reviews_page + "&take=" + (if isMobile then 2 else 3)).then (response) ->
                console.log(response)
                $scope.searching_reviews = false
                $scope.reviews = $scope.reviews.concat(response.data.reviews)
                $scope.has_more_reviews = response.data.has_more_reviews
                # if $scope.mobile then $timeout -> bindToggle()

        bindArguments($scope, arguments)
        $scope.selected_subject = '1'

        $scope.refreshSelect = ->
            $timeout ->
                $('.custom-select-sort').trigger('render')

        $scope.goSubject = (where) ->
            streamLink($scope.subject_routes[$scope.selected_subject], 'serp_' + where, $scope.findById($scope.subjects, $scope.selected_subject).eng)

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
