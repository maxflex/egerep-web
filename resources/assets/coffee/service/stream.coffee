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
                switch key
                    when 'sort'
                        switch parseInt(value)
                            when 2 then value = 'maxprice'
                            when 3 then value = 'minprice'
                            when 4 then value = 'rating'
                            when 5 then value = 'bymetro'
                            else value = 'pop'
                    when 'place'
                        switch parseInt(params.place)
                            when 1 then where = 'tutor'
                            when 2 then where = 'client'
                            else where = 'any'
                    when 'subjects' then value = SubjectService.getSelected(value).join(',')
                return if key in ['action', 'type', 'google_id', 'yandex_id', 'id', 'hidden_filter'] or not value
                parts.push(key + '=' + value)
            return parts.join('_')

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

            console.log(action, type, params)
            if action isnt 'view' then console.log(this.generateEventString(angular.copy(params)))
            if action isnt 'view' then dataLayerPush
                event: 'configuration'
                eventCategory: "action=#{action}" + (if type then "_type=#{type}" else "")
                eventAction: this.generateEventString(angular.copy(params))
            Stream.save(params).$promise

        this
