angular.module('Egerep')
    .factory 'Tutor', ($resource) ->
        $resource apiPath('tutors'), {id: '@id'},
            search:
                method: 'POST'
                url: apiPath('tutors', 'search')
            reviews:
                method: 'GET'
                isArray: true
                url: apiPath('tutors', 'reviews')
            view:
                method: 'GET'
                url: apiPath('tutors', 'view')

    .factory 'Request', ($resource) ->
        $resource apiPath('requests'), {id: '@id'}, updatable()

apiPath = (entity, additional = '') ->
    "api/#{entity}/" + (if additional then additional + '/' else '') + ":id"


updatable = ->
    update:
        method: 'PUT'
countable = ->
    count:
        method: 'GET'
