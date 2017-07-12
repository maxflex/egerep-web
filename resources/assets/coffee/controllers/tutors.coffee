angular
    .module 'Egerep'
    .constant 'REVIEWS_PER_PAGE', 5
    .controller 'Tutors', ($scope, $timeout, Tutor, SubjectService, REVIEWS_PER_PAGE, Request, StreamService, Sources) ->
        bindArguments($scope, arguments)

        # сколько загрузок преподавателей было
        search_count = 0

        $scope.popups = {}

        $scope.filterPopup = (popup) ->
            $scope.popups[popup] = true
            StreamService.run('filter_open', popup)

        # получить индекс преподавателя. если не указан, береш из хэша
        $scope.getIndex = (index = null) ->
            return (parseInt(index) + 1) if index isnt null
            $scope.index_from_hash or null

        $scope.streamLink = streamLink

        $scope.profileLink = (tutor, index, async = true) ->
            index = $scope.getIndex(index)
            link = "#{tutor.id}##{index}"
            window.open(link, '_blank') if async
            StreamService.run 'go_tutor_profile', StreamService.identifySource(tutor),
                position: index
                tutor_id: tutor.id
            .then ->
                window.location = link if not async

        # личная страница преподавателя?
        $scope.profilePage = ->
            RegExp(/^\/[\d]+$/).test(window.location.pathname)

        # страница поиска
        $timeout ->
            if not $scope.profilePage() and window.location.pathname isnt '/request'
                if $scope.page_was_refreshed and $.cookie('search') isnt undefined
                    id = $scope.search.id
                    $scope.search = JSON.parse($.cookie('search'))
                    $scope.search.id = id

                # если есть предустановленные предметы
                if $scope.selected_subjects
                    $scope.selected_subjects.split(',').forEach (subject_id) ->
                        $scope.search.subjects[subject_id] = true

                # place по умолчанию
                $scope.search.place = 1 if not $scope.search.place

                SubjectService.init($scope.search.subjects)
                StreamService.run('landing', 'serp')
                $scope.filter()
            else
                $scope.index_from_hash = window.location.hash.substring(1)
                StreamService.run 'landing', StreamService.identifySource(),
                    if $scope.profilePage() then {tutor_id: $scope.tutor.id, position: $scope.getIndex()} else {}

        # пары предметов
        $scope.pairs = [
            [1, 2]
            [3, 4]
            [6, 7]
            [8, 9]
        ]

        # просмотренные преподаватели (чтобы не засчитывались просмотры несколько раз)
        $scope.viewed_tutors = []

        # сотрудничает с 12 сентября 2000 года
        $scope.dateToText = (date) ->
            text_date = moment(date).format 'DD MMMM YYYY'
            # вырезаем дату, оставляем месяц и год
            # нужно именно так, чтобы осталось правильное склонение месяца
            text_date.substr(3)

        $scope.requestSent = (tutor) ->
            tutor.request_sent or $scope.sent_ids.indexOf(tutor.id) isnt -1

        $scope.gmap = (tutor, index) ->
            # if tutor.map_shown is undefined then
            # @todo: сделать так, чтобы ранее открытые карты не инициализировались заново
            $timeout ->
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

            $scope.toggleShow(tutor, 'map_shown', 'google_map', index)

        $scope.getMetros = (tutor) ->
            _.chain(tutor.markers).pluck('metros').flatten().value()

        $scope.reviews = (tutor, index) ->
            StreamService.run 'reviews', StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id
            if tutor.all_reviews is undefined
                tutor.all_reviews = Tutor.reviews
                    id: tutor.id
                , (response) ->
                    $scope.showMoreReviews(tutor)
            $scope.toggleShow(tutor, 'show_reviews', 'reviews', false)

        $scope.showMoreReviews = (tutor, index) ->
            if tutor.reviews_page then StreamService.run 'reviews_more', StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id
                depth: (tutor.reviews_page + 1) * REVIEWS_PER_PAGE
            tutor.reviews_page = if not tutor.reviews_page then 1 else (tutor.reviews_page + 1)
            from = (tutor.reviews_page - 1) * REVIEWS_PER_PAGE
            to = from + REVIEWS_PER_PAGE
            tutor.displayed_reviews = tutor.all_reviews.slice(0, to)
            highlight('search-result-reviews-text')

        $scope.reviewsLeft = (tutor) ->
            return if not tutor.all_reviews or not tutor.displayed_reviews
            reviews_left = tutor.all_reviews.length - tutor.displayed_reviews.length
            if reviews_left > REVIEWS_PER_PAGE then REVIEWS_PER_PAGE else reviews_left

        # чтобы не редиректило в начале
        filter_used = false
        $scope.filter = ->
            $scope.tutors = []
            $scope.page = 1
            if filter_used
                StreamService.updateCookie({search: StreamService.cookie.search + 1})
                StreamService.run 'filter', null,
                    search: StreamService.cookie.search
                    subjects: $scope.SubjectService.getSelected().join(',')
                    sort: $scope.search.sort
                    station_id: $scope.search.station_id
                    place: $scope.search.place
                .then -> filter()
            else
                filter()
                filter_used = true

        filter = ->
            $scope.popups = {}
            search()
            # деселект hidden_filter при смене параметров
            delete $scope.search.hidden_filter if $scope.search.hidden_filter and search_count
            $.cookie('search', JSON.stringify($scope.search))

        $scope.nextPage = ->
            $scope.page++
            StreamService.run('load_more_tutors', null, {page: $scope.page})
            search()

        $scope.$watch 'page', (newVal, oldVal) -> $.cookie('page', $scope.page) if newVal isnt undefined

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
                tutor_id: getUrlParam('tutor_id')
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

        $scope.showSvg = (tutor, index) ->
            $scope.toggleShow(tutor, 'show_svg', 'metro_map', index)

        # stream if index isnt null
        $scope.toggleShow = (tutor, prop, iteraction_type, index = null) ->
            if tutor[prop]
                $timeout ->
                    tutor[prop] = false
                , if $scope.mobile then 400 else 0
            else
                tutor[prop] = true
                if index isnt false then StreamService.run iteraction_type, StreamService.identifySource(tutor),
                    position: $scope.getIndex(index)
                    tutor_id: tutor.id

        $scope.shortenGrades = ->
            a = $scope.tutor.grades
            return if a.length < 1
            limit = a.length - 1
            combo_end = -1
            pairs = []
            i = 0
            while i <= limit
                combo_start = parseInt(a[i])

                if combo_start > 11
                    i++
                    combo_end = -1
                    pairs.push $scope.grades[combo_start].title
                    continue

                if combo_start <= combo_end
                    i++
                    continue

                j = i
                while j <= limit
                    combo_end = parseInt(a[j])
                    # если уже начинает искать по студентам
                    break if combo_end >= 11
                    break if parseInt(a[j + 1]) - combo_end > 1
                    j++
                if combo_start != combo_end
                    pairs.push combo_start + '–' + combo_end + ' классы'
                else
                    pairs.push combo_start + ' класс'
                i++
            pairs.join ', '

        #
        # MOBILE
        #
        $scope.popup = (id, tutor = null, fn = null, index = null) ->
            openModal(id)
            if tutor isnt null then $scope.popup_tutor = tutor
            if fn isnt null then $timeout -> $scope[fn](tutor, index)
            $scope.index = $scope.getIndex(index)

        $scope.request = (tutor, index) ->
            StreamService.run 'contact_tutor_button', StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id

        $scope.expand = (tutor, index) ->
            StreamService.run 'expand_tutor_info', StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id

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

        # выезжает по всей Москве
        $scope.departsEverywhere = (tutor) ->
            return false if tutor.svg_map is null
            # @todo: в мобильной версии svg_map – string, в стационарной – array
            tutor.svg_map = tutor.svg_map.split(',') if typeof tutor.svg_map is 'string'
            tutor.svg_map.length >= 214

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
