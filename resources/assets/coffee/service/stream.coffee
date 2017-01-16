angular.module 'Egerep'
    .service 'StreamService', ($http, $timeout, Stream, SubjectService, Sources) ->
        this.identifySource = (tutor = undefined) ->
            return 'similar' if tutor isnt undefined and tutor.is_similar
            return 'tutor' if RegExp(/^\/[\d]+$/).test(window.location.pathname)
            return 'help' if window.location.pathname is '/request'
            return 'main' if window.location.pathname is '/'
            return 'serp'

        this.generateEventString = (params) ->
            search = $.cookie('search')
            if search isnt undefined then $.each JSON.parse(search), (key, value) ->
                params[key] = value

            parts = []
            $.each params, (key, value) ->
                return if key in ['action', 'type', 'google_id', 'yandex_id', 'id'] or not value
                switch key
                    when 'sort'
                        switch parseInt(value)
                            when 2 then value = 'maxprice'
                            when 3 then value = 'minprice'
                            when 4 then value = 'rating'
                            when 5 then value = 'bymetro'
                            else value = 'pop'
                    when 'subjects' then value = SubjectService.getSelected(value).join(',')
                parts.push(key + '=' + value)

            return parts.join('_')
            return 'empty_' if this.search is undefined
            if this.subjects isnt null and params.subjects isnt ''
                subjects = []
                SubjectService.getSelected().forEach (subject_id) =>
                    subjects.push(this.subjects[subject_id].eng)
                subjects = subjects.join('+')
            else
                subjects = ''
            if params.place
                switch parseInt(params.place)
                    when 1 then where = 'tutor'
                    when 2 then where = 'client'
            else
                where = 'anywhere'

            switch parseInt(params.sort)
                when 2 then sort = 'maxprice'
                when 3 then sort = 'minprice'
                when 4 then sort = 'rating'
                when 5 then sort = 'bymetro'
                else sort = 'pop'

            metro = params.station_id or ''
            position = params.position or ''
            page = params.page or ''

            string = "search=#{params.search}_subj=#{subjects}_where=#{where}_sort=#{sort}_metro=#{metro}_page=#{page}_step=#{params.step}_"
            string += "position=#{position}_" if params.position
            $.each additional, (key, param) ->
                string += "#{key}=#{param}_"
            return string

        this.updateCookie = (params) ->
            this.cookie = {} if this.cookie is undefined
            $.each params, (key, value) =>
                this.cookie[key] = value
            $.cookie('stream', JSON.stringify(this.cookie), { expires: 365, path: '/' })

        this.initCookie = ->
            if $.cookie('stream') isnt undefined
                this.cookie = JSON.parse($.cookie('stream'))
            else
                this.updateCookie({step: 0, search: 0})

        this.run = (action, type, additional = {}) ->
            this.initCookie() if this.cookie is undefined
            if not this.initialized
                $timeout =>
                    this._run(action, type, additional)
                , 500
            else
                this._run(action, type, additional)

        this._run = (action, type, additional = {}) ->
            this.updateCookie({step: this.cookie.step + 1})

            params =
                action: action
                type: type
                step: this.cookie.step
                google_id: googleClientId()
                yandex_id: yaCounter1411783.getClientID()
                mobile: if (typeof isMobile is 'undefined') then '0' else '1'

            $.each additional, (key, value) =>
                params[key] = value

            # if this.search isnt undefined
            #     params.place      = this.search.place
            #     params.station_id = this.search.station_id
            #     params.sort       = this.search.sort
            #     params.subjects   = SubjectService.getSelected().join(',')
            #     params.page       = $.cookie('page') or 1

            # params.position = position if position isnt null

            console.log(action, type, params)
            if action isnt 'view' then console.log(this.generateEventString(angular.copy(params)))
            if action isnt 'view' then dataLayerPush
                event: 'configuration'
                eventCategory: "action=#{action}" + (if type then "_type=#{type}" else "")
                eventAction: this.generateEventString(angular.copy(params))
            Stream.save(params).$promise
            # console.log(source, this.generateEventString(params))

        this
