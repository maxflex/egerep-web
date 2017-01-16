angular.module('Egerep')
    .factory 'Tutor', ($resource) ->
        $resource apiPath('tutors'), {id: '@id', type: '@type'},
            search:
                method: 'POST'
                url: apiPath('tutors', 'search')
            reviews:
                method: 'GET'
                isArray: true
                url: apiPath('tutors', 'reviews')
            login:
                method: 'GET'
                url: apiPath('tutors', 'login')

    .factory 'Request', ($resource) ->
        $resource apiPath('requests'), {id: '@id'}, updatable()

    .factory 'Sms', ($resource) ->
        $resource apiPath('sms'), {id: '@id'}, updatable()

    .factory 'Cv', ($resource) ->
        $resource apiPath('cv'), {id: '@id'}

    .factory 'Stream', ($resource) ->
        $resource apiPath('stream'), {id: '@id'}

apiPath = (entity, additional = '') ->
    "/api/#{entity}/" + (if additional then additional + '/' else '') + ":id"


updatable = ->
    update:
        method: 'PUT'
countable = ->
    count:
        method: 'GET'
