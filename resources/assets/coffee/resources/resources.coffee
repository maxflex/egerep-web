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
            iteraction:
                method: 'GET'
                url: "/api/tutors/iteraction/:id/:type"

    .factory 'Request', ($resource) ->
        $resource apiPath('requests'), {id: '@id'}, updatable()

apiPath = (entity, additional = '') ->
    "/api/#{entity}/" + (if additional then additional + '/' else '') + ":id"


updatable = ->
    update:
        method: 'PUT'
countable = ->
    count:
        method: 'GET'
