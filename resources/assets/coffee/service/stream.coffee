angular.module 'Egerep'
    .service 'Stream', ->
        this.init = (search, page) ->
            this.search = search
            this.page = page
        this
