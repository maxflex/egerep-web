angular
    .module 'Egerep'
    .constant 'REVIEWS_PER_PAGE', 5
    .controller 'Tutors', ($scope, $http, $timeout, Tutor, SubjectService, REVIEWS_PER_PAGE, Genders, Request, StreamService, Sources, StepperService) ->
        bindArguments($scope, arguments)

        # сколько загрузок преподавателей было
        search_count = 0

        $scope.popups = {}

        $scope.station_ids = {}

        $scope.paramsCount = ->
            count = 0
            count++ if $scope.search.hasOwnProperty('subjects') && Object.keys($scope.search.subjects).length
            count++ if $scope.search.hasOwnProperty('sort') && $scope.search.sort
            count++ if $scope.search.hasOwnProperty('place') && $scope.search.place
            # count++ if $scope.search.hasOwnProperty('station_id') && $scope.search.station_id
            count

        $scope.filterPopup = (popup) ->
            if Object.keys($scope.popups).length
                $scope.popups = {}
                return
            $scope.popups[popup] = true
            openModal("filter-#{popup}") if $scope.mobile
            StreamService.run('filter_open', popup)
            if popup is 'all' && $scope.hasOwnProperty('is_first_visit')
                data =
                    event: 'configuration'
                    eventCategory: 'ex:open-stepper'
                dataLayerPush(data)
                console.log(data)

        # получить рейтинг с отклонием
        $scope.getStarRating = (rating) ->
            segment = (Math.floor(rating / 2) * 2) + 1
            (segment - ((segment - rating) * 0.6)) * 10

        # получить индекс преподавателя. если не указан, береш из хэша
        $scope.getIndex = (index = null) ->
            return (parseInt(index) + 1) if index isnt null
            $scope.index_from_hash or null

        $scope.streamLink = streamLink

        $scope.profileLink = (tutor, index, async = true, event_name = 'tutor_profile') ->
            index = $scope.getIndex(index)
            link = "#{tutor.id}"
            link += "##{index}" if index
            window.open(link, '_blank') if async
            StreamService.run 'go_' + event_name, StreamService.identifySource(tutor),
                position: index
                tutor_id: tutor.id
            .then ->
                window.location = link if not async

        # личная страница преподавателя?
        $scope.profilePage = ->
            RegExp(/^\/[\d]+$/).test(window.location.pathname)

        # $scope.objectSelectedCallback = (selected_station) ->
        #     if selected_station
        #         $scope.search.station_id = selected_station.originalObject.id
        #         $scope.$broadcast('angucomplete-alt:changeInput', 'stations-autocomplete', $scope.$eval("stations[search.station_id].title | cut:false:13:'...'"))
        #     else
        #         $scope.search.station_id = null
        #         $scope.$broadcast('angucomplete-alt:changeInput', 'stations-autocomplete', '')

        # $scope.clearStation = ->
        #     $scope.$broadcast('angucomplete-alt:clearInput');
        #     $scope.search.station_id = null
        #     $timeout -> $('.autocomplete-input').focus()


        bindWatchers = ->
            $scope.$watchCollection 'search.subjects', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'subjects'
                    eventAction: $scope.SubjectService.getSelected().map((subject_id) -> $scope.subjects[subject_id].eng).join(',')
                dataLayerPush(data)
                console.log(data)

            $scope.$watchCollection 'search.place', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'place'
                    eventAction: $scope.search.place
                dataLayerPush(data)
                console.log(data)

            $scope.$watchCollection 'search.sort', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'sort'
                    eventAction: $scope.search.sort
                dataLayerPush(data)
                console.log(data)

            $scope.$watchCollection 'search.station_id', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'station'
                    eventAction: if $scope.search.station_id then $scope.stations[$scope.search.station_id].title else ''
                dataLayerPush(data)
                console.log(data)

        bindWatchersDev = ->
            $scope.$watchCollection 'search.subjects', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'ex:subjects'
                    eventAction: $scope.SubjectService.getSelected().map((subject_id) -> $scope.subjects[subject_id].eng).join(',')
                dataLayerPush(data)
                console.log(data)

            $scope.$watch 'search.place', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'ex:place'
                    eventAction: $scope.search.place
                dataLayerPush(data)
                console.log(data)

            $scope.$watch 'search.sort', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'ex:sort'
                    eventAction: $scope.search.sort
                dataLayerPush(data)
                console.log(data)

            $scope.$watch 'search.station_id', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'ex:station'
                    eventAction: if $scope.search.station_id then $scope.stations[$scope.search.station_id].title else ''
                dataLayerPush(data)
                console.log(data)

            $scope.$watch 'search.grade', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'ex:grade'
                    eventAction: $scope.search.grade
                dataLayerPush(data)
                console.log(data)

            $scope.$watch 'search.preparation_direction', (newVal, oldVal) ->
                return if newVal is oldVal
                data =
                    event: 'configuration'
                    eventCategory: 'ex:preparation_direction'
                    eventAction: $scope.search.preparation_direction
                dataLayerPush(data)
                console.log(data)

        handleScrollDesktop = ->
            if not $scope.hasOwnProperty('is_first_visit')
                wrapper = $('.filter-groups')
                sticky = wrapper.position().top - 1
                $(window).on 'scroll', ->
                    if window.pageYOffset > sticky then $('body').addClass('sticky') else $('body').removeClass('sticky')

        handleScrollMobile = ->
            # sticky = $('.filter-full-width').position().top - 1
            # $(window).on 'scroll', ->
            #     wrapper = $('.filter-full-width')
            #     if window.pageYOffset > sticky
            #         wrapper.addClass('sticky')
            #         $('.accordions').addClass('sticky')
            #     else
            #         wrapper.removeClass('sticky')
            #         $('.accordions').removeClass('sticky')

        # страница поиска
        $timeout ->
            $timeout((if $scope.hasOwnProperty('is_first_visit') then bindWatchersDev else bindWatchers), 500)
            if not $scope.profilePage() and window.location.pathname isnt '/request'
                # if $scope.page_was_refreshed and $.cookie('search') isnt undefined
                #     id = $scope.search.id
                #     $scope.search = JSON.parse($.cookie('search'))
                #     $scope.search.id = id

                # если есть предустановленные предметы
                if $scope.selected_subjects
                    $scope.selected_subjects.split(',').forEach (subject_id) ->
                        $scope.search.subjects[subject_id] = true

                # place по умолчанию
                # $scope.search.place = 1 if not $scope.search.place

                # if ($scope.search.priority == '2' || $scope.search.priority == '3')
                #     $scope.station_ids[$scope.search.priority] = $scope.search.station_id

                SubjectService.init($scope.search.subjects)
                StreamService.run('landing', 'serp')
                if $scope.hasOwnProperty('is_first_visit')
                    $scope.filter() if not $scope.is_first_visit
                else
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

        $scope.requestSent = (tutor) ->
            tutor.request_sent or $scope.sent_ids.indexOf(tutor.id) isnt -1

        # directionsService = new google.maps.DirectionsService()
        # directionsDisplay = new google.maps.DirectionsRenderer()
        $scope.gmap = (tutor, index) ->
            tutor.map_initialized = true
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

                # directionsDisplay.setMap(map)

                bounds = new (google.maps.LatLngBounds)
                tutor.markers.forEach (marker) ->
                    marker_location = new google.maps.LatLng(marker.lat, marker.lng)
                    # closest_metro = marker.metros[0]
                    bounds.extend(marker_location)
                    new_marker = newMarker(marker_location, map)

                # one marker bug fix
                if bounds.getNorthEast().equals(bounds.getSouthWest())
                    extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.005, bounds.getNorthEast().lng() + 0.005)
                    extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.005, bounds.getNorthEast().lng() - 0.005)
                    bounds.extend(extendPoint1)
                    bounds.extend(extendPoint2)

                map.fitBounds bounds
                map.panToBounds bounds
                map.setZoom if tutor.markers.length > 1 then 9 else 14

                # bug fix – убираем квадратик в правом нижнем углу
                google.maps.event.addListenerOnce map, 'idle', ->
                    $('div:has(>a[href^="https://www.google.com/maps"])').remove()

            $scope.toggleShow(tutor, 'map_shown', 'google_map', index)

        $scope.getMetros = (tutor) ->
            _.chain(tutor.markers).pluck('metros').flatten().value()

        $scope.loadReviews = (tutor) ->
            if tutor.all_reviews is undefined
                tutor.reviews_loading = true
                tutor.all_reviews = Tutor.reviews
                    id: tutor.id
                , (response) ->
                    tutor.reviews_loading = false
                    $scope.showMoreReviews(tutor)

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
            if tutor.all_reviews is undefined
                $scope.loadReviews(tutor)
                return
            if tutor.reviews_page then StreamService.run 'reviews_more', StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id
                depth: (tutor.reviews_page + 1) * REVIEWS_PER_PAGE + (if tutor.reviews_page == 1 then 2 else 0)
            tutor.reviews_page = if not tutor.reviews_page then 1 else (tutor.reviews_page + 1)
            from = (tutor.reviews_page - 1) * REVIEWS_PER_PAGE + 2
            to = from + REVIEWS_PER_PAGE
            tutor.displayed_reviews = tutor.all_reviews.slice(0, to)
            highlight('search-result-reviews-text')

        $scope.reviewsLeft = (tutor) ->
            reviews_left = tutor.reviews_count - tutor.displayed_reviews.length
            if reviews_left > REVIEWS_PER_PAGE then REVIEWS_PER_PAGE else reviews_left

        $scope.stepperFilter = ->
            closeStepper()
            $scope.filter()
            $scope.is_first_visit = false
            data =
                event: 'configuration'
                eventCategory: 'ex:show-tutors'
            dataLayerPush(data)
            console.log(data)

        # чтобы не редиректило в начале
        filter_used = false
        $scope.filter = (type = null) ->
            $scope.popups = {}
            closeModal()
            $scope.tutors = []
            $scope.page = 1
            if filter_used
                StreamService.updateCookie({search: StreamService.cookie.search + 1})
                if true
                    params =
                        search: StreamService.cookie.search
                        subjects: $scope.SubjectService.getSelected().join(',')
                        station_id: $scope.search.station_id
                        sort: $scope.search.sort
                        place: $scope.search.place
                else
                    params =
                        search: StreamService.cookie.search
                        subjects: $scope.SubjectService.getSelected().join(',')
                        station_id: $scope.search.station_id
                        priority: $scope.search.priority
                StreamService.run('filter', type, params).then -> filter()
            else
                filter()
                filter_used = true

        filter = ->
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
                if subject_id
                    pair = _.filter scope.pairs, (p) ->
                        p.indexOf(parseInt(subject_id)) isnt -1
                    pair.push([subject_id]) if not pair.length
                    $scope.search.subjects[id] = false if pair[0].indexOf(parseInt(id)) is -1
                else
                    $scope.search.subjects[id] = false

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
                if search_count == 1
                    $timeout ->
                        if $scope.mobile then handleScrollMobile() else handleScrollDesktop()
                    , 500
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
                    $timeout ->
                        window.dispatchEvent(new Event('scroll'))
                        $('html, body').scrollTop(0) if $scope.page == 1


        # highlight hidden filter
        highlight = (className)->
            if $scope.search and $scope.search.hidden_filter then $timeout ->
                $.each $scope.search.hidden_filter, (index, phrase) ->
                    $(".#{className}").mark phrase,
                        separateWordSearch: true
                        accuracy:
                            value: 'exactly'
                            limiters: ['!', '@', '#', '&', '*', '(', ')', '-', '–', '—', '+', '=', '[', ']', '{', '}', '|', ':', ';', '\'', '\"', '‘', '’', '“', '”', ',', '.', '<', '>', '/', '?']

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

        $scope.shortenGrades = (tutor) ->
            a = tutor.grades
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

        $scope.roundRating = (review) -> Math.round(review.score / 2)

        $scope.popup = (id, tutor = null, fn = null, index = null) ->
            openModal(id)
            if tutor isnt null then $scope.popup_tutor = tutor
            if fn isnt null then $timeout -> $scope[fn](tutor, index)
            $scope.index = $scope.getIndex(index)

        $scope.request = (tutor, index) ->
            StreamService.run 'contact_tutor_button', StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id

        $scope.expandMoreInfo = (tutor, index) ->
            tutor.expand_info = true
            StreamService.run 'expand_more_tutor_info', StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id

        $scope.expand = (tutor, index) ->
            tutor.is_expanded = !tutor.is_expanded
            $scope.gmap(tutor, index) if not tutor.map_initialized
            event_name = if tutor.is_expanded then 'expand_tutor_info' else 'shrink_tutor_info'
            StreamService.run event_name, StreamService.identifySource(tutor),
                position: $scope.getIndex(index)
                tutor_id: tutor.id

        $scope.tutorPopup = (tutor, index) ->
            $('#modal-tutor .modal-content').scrollTop(0)
            $scope.gmap(tutor, index)

        # $scope.setPriority = (priority_id) ->
        #     $scope.search.priority = priority_id if priority_id != 2 && priority_id != 3
        #
        # $scope.syncSort = (priority_id) ->
        #     if priority_id == 2 || priority_id == 3
        #         if not $scope.station_ids[priority_id]
        #             $scope.search.priority = 1
        #             return
        #         $scope.search.station_id = $scope.station_ids[priority_id]
        #     $scope.search.priority = priority_id

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
            return false if not tutor.svg_map
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
