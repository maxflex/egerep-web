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
        
        $scope.roundRating = (review) -> Math.round(review.score / 2)

        # $scope.$watch 'page', (newVal, oldVal) -> $.cookie('page', $scope.page) if newVal isnt undefined

        searchReviews = ->
            $scope.searching_reviews = true
            $http.get('/api/reviews?page=' + $scope.reviews_page).then (response) ->
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

        $scope.onWebsite = (tutor, type = 'month') ->
            return if not tutor
            current_year = parseInt(moment().format('YYYY'))
            attachment_year = parseInt(moment(tutor.created_at).format('YYYY'))

            current_month = parseInt(moment().format('M'))
            attachment_month = parseInt(moment(tutor.created_at).format('M'))

            month_diff = current_month - attachment_month
            year_diff = current_year - attachment_year

            # если месяц отрицательный
            if month_diff < 0
                month_diff = 12 + month_diff
                year_diff--

            if type is 'month' then month_diff else year_diff

        $scope.randomReview = ->
            $scope.loading_review = true
            $http.get 'api/reviews/random'
            .then (response) ->
                $scope.random_review = response.data
                $scope.loading_review = false
