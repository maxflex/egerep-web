angular
    .module 'Egerep'
    .constant 'REVIEWS_PER_PAGE', 5
    .controller 'Tutors', ($scope, $timeout, Tutor, REVIEWS_PER_PAGE) ->
        bindArguments($scope, arguments)

        # сколько загрузок преподавателей было
        search_count = 0

        # личная страница преподавателя?
        $scope.profilePage = ->
            RegExp(/^\/[\d]+$/).test(window.location.pathname)

        if not $scope.profilePage()
        # страница поиска
            $timeout ->
                # если есть предустановленные предметы
                if $scope.selected_subjects
                    $scope.selected_subjects.split(',').forEach (subject_id) ->
                        $scope.search.subjects[subject_id] = true
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

        # сотрудничает с 12 сентября 2000 года
        $scope.dateToText = (date) ->
            text_date = moment(date).format 'DD MMMM YYYY'
            # вырезаем дату, оставляем месяц и год
            # нужно именно так, чтобы осталось правильное склонение месяца
            text_date.substr(3)

        $scope.requestSent = (tutor) ->
            tutor.request_sent or $scope.sent_ids.indexOf(tutor.id) isnt -1

        $scope.gmap = (tutor) ->
            if tutor.map_shown is undefined then $timeout ->
                map = new google.maps.Map document.getElementById("gmap-#{tutor.id}"),
                    center: MAP_CENTER
                    scrollwheel: false,
                    zoom: 8
                    disableDefaultUI: true
                    clickableLabels: false
                    clickableIcons: false
                    zoomControl: true
                    zoomControlOptions:
                        position: google.maps.ControlPosition.LEFT_BOTTOM
                    scaleControl: true

                bounds = new (google.maps.LatLngBounds)
                tutor.markers.forEach (marker) ->
                    bounds.extend(new google.maps.LatLng(marker.lat, marker.lng))
                    new_marker = newMarker(new google.maps.LatLng(marker.lat, marker.lng), map)

                # one marker bug fix
                if bounds.getNorthEast().equals(bounds.getSouthWest())
                    extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.02, bounds.getNorthEast().lng() + 0.02)
                    extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.02, bounds.getNorthEast().lng() - 0.02)
                    bounds.extend(extendPoint1)
                    bounds.extend(extendPoint2)

                map.fitBounds bounds
                map.panToBounds bounds
                map.setZoom 12

                # bug fix – убираем квадратик в правом нижнем углу
                google.maps.event.addListenerOnce map, 'idle', ->
                    $('div:has(>a[href^="https://www.google.com/maps"])').remove()

            $scope.toggleShow(tutor, 'map_shown', 'gmap')

        $scope.getMetros = (tutor) ->
            _.chain(tutor.markers).pluck('metros').flatten().value()

        $scope.reviews = (tutor) ->
            if tutor.all_reviews is undefined
                tutor.all_reviews = Tutor.reviews
                    id: tutor.id
                , (response) ->
                    $scope.showMoreReviews(tutor)
            $scope.toggleShow(tutor, 'show_reviews', 'reviews')

        $scope.showMoreReviews = (tutor) ->
            Tutor.iteraction {id: tutor.id, type: 'reviews_more'} if tutor.reviews_page
            tutor.reviews_page = if not tutor.reviews_page then 1 else (tutor.reviews_page + 1)
            from = (tutor.reviews_page - 1) * REVIEWS_PER_PAGE
            to = from + REVIEWS_PER_PAGE
            tutor.displayed_reviews = tutor.all_reviews.slice(0, to)
            highlight('search-result-reviews-text')

        $scope.reviewsLeft = (tutor) ->
            tutor.all_reviews.length - tutor.displayed_reviews.length

        $scope.countView = (tutor_id) ->
            if viewed_tutors.indexOf(tutor_id) is -1
                # Tutor.iteraction {id: tutor_id, type: 'views'}
                viewed_tutors.push tutor_id

        $scope.filter = (subject_id) ->
            $scope.tutors = []
            unselectSubjects(subject_id)
            $scope.page = 1
            search()
            # деселект hidden_filter при смене параметров
            delete $scope.search.hidden_filter if $scope.search.hidden_filter and search_count

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
                search_count++
                $scope.searching = false
                if response.hasOwnProperty('url')
                    redirect response.url
                else
                    $scope.data = response
                    $scope.tutors = $scope.tutors.concat(response.data)
                    #comma separated fields
                    angular.forEach $scope.tutors, (tutor) ->
                        tutor.svg_map = _.filter tutor.svg_map.split(',') if 'string' == typeof tutor.svg_map
                    highlight('search-result-text')

        # highlight hidden filter
        highlight = (className)->
            if $scope.search.hidden_filter then $timeout ->
                $.each $scope.search.hidden_filter, (index, phrase) ->
                    $(".#{className}").mark phrase,
                        separateWordSearch: true
                        accuracy: 'exactly'

        $scope.clearMetro = ->
            $('.search-metro-autocomplete').val('')
            $('.search-filter-metro-wrap').removeClass('active')
            $scope.search.station_id = 0
            $scope.search.sort = '1'
            $scope.filter()
            $timeout ->
                $('.custom-select-sort').trigger('render')

        $scope.showSvg = (tutor) ->
            if tutor.show_svg is undefined
                map = new SVGMap
                    iframeId: 'svg-iframe-'+tutor.id
                    clicable: false
                    places: $scope.svg
                map.init()
                map.deselectAll()
                map.select(tutor.svg_map)
            $scope.toggleShow(tutor, 'show_svg', 'svg_map')

        $scope.toggleShow = (tutor, prop, iteraction_type) ->
            if tutor[prop]
                tutor[prop] = false
            else
                tutor[prop] = true
                Tutor.iteraction {id: tutor.id, type: iteraction_type}
