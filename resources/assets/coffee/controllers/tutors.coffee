angular
    .module 'Egerep'
    .constant 'REVIEWS_PER_PAGE', 5
    .controller 'Tutors', ($scope, $timeout, Tutor, SubjectService, REVIEWS_PER_PAGE, Request, Stream) ->
        bindArguments($scope, arguments)

        # сколько загрузок преподавателей было
        search_count = 0

        # личная страница преподавателя?
        $scope.profilePage = ->
            RegExp(/^\/[\d]+$/).test(window.location.pathname)

        if not $scope.profilePage()
        # страница поиска
            $timeout ->
                if $scope.page_was_refreshed and $.cookie('search') isnt undefined
                    id = $scope.search.id
                    $scope.search = JSON.parse($.cookie('search'))
                    $scope.search.id = id

                # если есть предустановленные предметы
                if $scope.selected_subjects
                    $scope.selected_subjects.split(',').forEach (subject_id) ->
                        $scope.search.subjects[subject_id] = true

                SubjectService.init($scope.search.subjects)
                $scope.filter()

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
                    extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.005, bounds.getNorthEast().lng() + 0.005)
                    extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.005, bounds.getNorthEast().lng() - 0.005)
                    bounds.extend(extendPoint1)
                    bounds.extend(extendPoint2)

                map.fitBounds bounds
                map.panToBounds bounds
                map.setZoom if tutor.markers.length > 1 then 11 else 16

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
            return if not tutor.all_reviews or not tutor.displayed_reviews
            reviews_left = tutor.all_reviews.length - tutor.displayed_reviews.length
            if reviews_left > REVIEWS_PER_PAGE then REVIEWS_PER_PAGE else reviews_left

        $scope.countView = (tutor_id) ->
            if viewed_tutors.indexOf(tutor_id) is -1
                # Tutor.iteraction {id: tutor_id, type: 'views'}
                viewed_tutors.push tutor_id

        # чтобы не редиректило в начале
        filter_used = false
        $scope.filter = ->
            $scope.tutors = []
            $scope.page = 1
            search()
            # деселект hidden_filter при смене параметров
            delete $scope.search.hidden_filter if $scope.search.hidden_filter and search_count
            $.cookie('search', JSON.stringify($scope.search))
            filter_used = true

        $scope.nextPage = ->
            $scope.page++
            search()

        $scope.isLastPage = ->
            return if not $scope.data
            $scope.data.current_page >= $scope.data.last_page

        $scope.unselectSubjects = (subject_id) ->
            angular.forEach $scope.search.subjects, (enabled, id) ->
                pair = _.filter scope.pairs, (p) ->
                    p.indexOf(parseInt(subject_id)) isnt -1
                pair.push([subject_id]) if not pair.length
                $scope.search.subjects[id] = false if pair[0].indexOf(parseInt(id)) is -1

        search = ->
            $scope.searching = true
            Tutor.search
                filter_used: filter_used
                page: $scope.page
                search: $scope.search
            , (response) ->
                search_count++
                $scope.searching = false
                if response.hasOwnProperty('url')
                    console.log 'redirectring...'
                    redirect response.url
                    # console.log response.url
                else
                    $scope.data = response
                    $scope.tutors = $scope.tutors.concat(response.data)
                    #comma separated fields
                    angular.forEach $scope.tutors, (tutor) ->
                        tutor.svg_map = _.filter tutor.svg_map.split(',') if 'string' == typeof tutor.svg_map
                    highlight('search-result-text')
                    if $scope.mobile then $timeout -> bindToggle()

        # highlight hidden filter
        highlight = (className)->
            if $scope.search and $scope.search.hidden_filter then $timeout ->
                $.each $scope.search.hidden_filter, (index, phrase) ->
                    $(".#{className}").mark phrase,
                        separateWordSearch: true
                        accuracy:
                            value: 'exactly'
                            limiters: ['!', '@', '#', '&', '*', '(', ')', '-', '–', '—', '+', '=', '[', ']', '{', '}', '|', ':', ';', '\'', '\"', '‘', '’', '“', '”', ',', '.', '<', '>', '/', '?']

        $scope.clearMetro = ->
            $('.search-metro-autocomplete').val('')
            $('.search-filter-metro-wrap').removeClass('active')
            $scope.search.station_id = 0
            $scope.search.sort = '1'
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
                # if $scope.mobile then $timeout ->
                #     svg = $("#svg-#{tutor.id}")
                #     svg.scrollLeft(svg.width() / 4).scrollTop(svg.height() / 2)
            $scope.toggleShow(tutor, 'show_svg', 'svg_map')

        $scope.toggleShow = (tutor, prop, iteraction_type) ->
            if tutor[prop]
                $timeout ->
                    tutor[prop] = false
                , if $scope.mobile then 400 else 0
            else
                tutor[prop] = true
                Tutor.iteraction {id: tutor.id, type: iteraction_type}

        #
        # MOBILE
        #
        $scope.popup = (id, tutor = null, fn = null) ->
            openModal(id)
            if tutor isnt null then $scope.popup_tutor = tutor
            if fn isnt null then $timeout -> $scope[fn](tutor)

        $scope.syncSort = ->
            $scope.search.sort = if $scope.search.station_id then 5 else 1

        $scope.changeFilter = (param, value = null) ->
            $scope.search[param] = value if value isnt null
            $scope.overlay[param] = false
            $scope.filter()

        # выезжает на выбранную станцию
        $scope.hasSelectedStation = (tutor) ->
            return false if not $scope.search or $scope.search.sort != 5
            tutor.svg_map.indexOf(parseInt($scope.search.station_id)) isnt -1

        $scope.sendRequest = ->
            $scope.sending_tutor.request = {} if $scope.sending_tutor.request is undefined
            $scope.sending_tutor.request.tutor_id = $scope.sending_tutor.id
            Request.save $scope.sending_tutor.request, ->
                $scope.sending_tutor.request_sent = true
            , (response) ->
                if response.status is 422
                    angular.forEach response.data, (errors, field) ->
                        selector = "[ng-model$='#{field}']"
                        $('.request-overlay').find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                else
                    $scope.sending_tutor.request_error = true

        angular.element(document).ready ->
            if $scope.mobile then $timeout -> bindToggle()
