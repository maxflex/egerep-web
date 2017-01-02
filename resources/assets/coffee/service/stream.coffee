angular.module 'Egerep'
    .service 'StreamService', ($http, $timeout, Stream, SubjectService, Sources) ->
        identifySource = ->
            return Sources.LANDING_PROFILE if RegExp(/^\/[\d]+$/).test(window.location.pathname)
            return Sources.LANDING_HELP if window.location.pathname is '/request'
            return Sources.LANDING

        this.generateEventString = (params, additional) ->
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

        this.init = (search, subjects = null) ->
            $timeout =>
                if search isnt undefined
                    SubjectService.init(search.subjects)
                    this.search = search
                this.subjects = subjects
                if $.cookie('stream') isnt undefined
                    this.cookie = JSON.parse($.cookie('stream'))
                else
                    this.updateCookie({step: 0, search: 0})
                this.run(identifySource())
            , 500

        this.run = (source, position = null, additional = {}) ->
            $timeout =>
                this.updateCookie({step: this.cookie.step + 1})

                params =
                    source:     source
                    search:     this.cookie.search
                    step:       this.cookie.step
                    client_id:  googleClientId()

                if this.search isnt undefined
                    params.place      = this.search.place
                    params.station_id = this.search.station_id
                    params.sort       = this.search.sort
                    params.subjects   = SubjectService.getSelected().join(',')
                    params.page       = $.cookie('page') or 1

                params.position = position if position isnt null

                Stream.save(params)

                dataLayerPush
                    event: 'configuration'
                    eventCategory: source
                    eventAction: this.generateEventString(params, additional)
                console.log(source, this.generateEventString(params, additional))
            , 500

        this
