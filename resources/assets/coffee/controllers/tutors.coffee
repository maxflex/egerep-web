angular
    .module 'Egerep'
    .constant 'REVIEWS_PER_PAGE', 5
    .controller 'TutorProfile', ($scope) ->
        console.log 1
    .controller 'Tutors', ($scope, $timeout, Tutor, Request, REVIEWS_PER_PAGE) ->
        bindArguments($scope, arguments)

        # страница преподавателя
        if window.location.pathname.indexOf('/tutor/') is 0
            console.log 'here'
        else
        # страница поиска
            $timeout ->
                $scope.chunked_subjects = chunk(toArray($scope.subjects), 4)
                metroAutocomplete($scope)
                $scope.filter() if not parseInt($scope.search.station_id)

        # пары предметов
        $scope.pairs = [
            [1, 2]
            [3, 4]
            [6, 7]
            [8, 9]
        ]

        # просмотренные преподаватели (чтобы не засчитывались просмотры несколько раз)
        viewed_tutors = []

        # отправить заявку
        $scope.request = (tutor) ->
            tutor.request.tutor_id = tutor.id
            Request.save tutor.request, ->
                tutor.request_sent = true
            console.log tutor

        $scope.gmap = (tutor) ->
            if tutor.map_shown is undefined then $timeout ->
                map = new google.maps.Map document.getElementById("gmap-#{tutor.id}"),
                    center: MAP_CENTER
                    scrollwheel: false,
                    zoom: 8

                bounds = new (google.maps.LatLngBounds)
                tutor.markers.forEach (marker) ->
                    bounds.extend(new google.maps.LatLng(marker.lat, marker.lng))
                    new_marker = newMarker(new google.maps.LatLng(marker.lat, marker.lng), map)
                map.fitBounds bounds
                map.panToBounds bounds
                map.setZoom 12
            $scope.toggleShow(tutor, 'map_shown', 'gmap')

        $scope.getMetros = (tutor) ->
            _.chain(tutor.markers).pluck('metros').flatten().value()

        $scope.reviews = (tutor) ->
            Tutor.iteraction {id: tutor.id, type: 'reviews'}
            tutor.all_reviews = Tutor.reviews
                id: tutor.id
            , (response) ->
                $scope.showMoreReviews(tutor)

        $scope.showMoreReviews = (tutor) ->
            Tutor.iteraction {id: tutor.id, type: 'reviews_more'}
            tutor.reviews_page = if not tutor.reviews_page then 1 else (tutor.reviews_page + 1)
            from = (tutor.reviews_page - 1) * REVIEWS_PER_PAGE
            to = from + REVIEWS_PER_PAGE
            tutor.displayed_reviews = tutor.all_reviews.slice(0, to)

        $scope.reviewsLeft = (tutor) ->
            tutor.all_reviews.length - tutor.displayed_reviews.length

        $scope.countView = (tutor_id) ->
            if viewed_tutors.indexOf(tutor_id) is -1
                Tutor.iteraction {id: tutor_id, type: 'views'}
                viewed_tutors.push tutor_id

        $scope.filter = (subject_id) ->
            $scope.tutors = []
            unselectSubjects(subject_id)
            $scope.page = 1
            search()

        $scope.nextPage = ->
            $scope.page++
            search()

        $scope.isLastPage = ->
            return if not $scope.data
            $scope.data.current_page >= $scope.data.last_page

        unselectSubjects = (subject_id) ->
            if subject_id
                angular.forEach $scope.search.subjects, (enabled, id) ->
                    pair = _.filter scope.pairs, (p) ->
                        p.indexOf(parseInt(subject_id)) isnt -1
                    pair.push([subject_id]) if not pair.length
                    $scope.search.subjects[id] = false if pair[0].indexOf(parseInt(id)) is -1

        search = ->
            $scope.searching = true
            Tutor.search
                page: $scope.page
                search: $scope.search
            , (response) ->
                $scope.searching = false
                if response.hasOwnProperty('url')
                    redirect response.url
                else
                    $scope.data = response
                    $scope.tutors = $scope.tutors.concat(response.data)
                    #comma separated fields
                    angular.forEach $scope.tutors, (tutor) ->
                        tutor.svg_map = _.filter tutor.svg_map.split(',') if 'string' == typeof tutor.svg_map

        $scope.clearMetro = ->
            $('.search-metro-autocomplete').val('')
            $('.search-filter-metro-wrap').removeClass('active')
            $scope.search.station_id = 0
            $scope.filter()

        $scope.showSvg = (tutor) ->
            if tutor.show_svg is undefined
                map = new SVGMap
                    iframeId: 'svg-iframe-'+tutor.id
                    clicable: false
                    places: $scope.svg
                map.init()
                map.deselectAll()
                map.select(tutor.svg_map);
                # $(document.getElementById('svg-iframe-'+tutor.id).contentWindow.document).keydown (e) ->
                #     if e.keyCode == 27
                #         setTimeout ->
                #             $('#svg-' + tutor.id).hide()
                #         , 200
                # return
            $scope.toggleShow(tutor, 'show_svg', 'svg_map')

        $scope.toggleShow = (tutor, prop, iteraction_type) ->
            if tutor[prop]
                tutor[prop] = false
            else
                tutor[prop] = true
                Tutor.iteraction {id: tutor.id, type: iteraction_type}

        $scope.subjectsLink = (tutor) ->
            link = ['tutors']
            tutor.subjects.forEach (subject_id) ->
                link.push $scope.subjects[subject_id].eng
            '/' + link.join('-')
