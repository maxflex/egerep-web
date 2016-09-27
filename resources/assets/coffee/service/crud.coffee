# angular.module 'Egecms'
#     .service 'IndexService', ($rootScope) ->
#         this.max_size = 30
#
#         this.init = (Resource, current_page, attrs) ->
#             $rootScope.frontend_loading = true
#             this.Resource = Resource
#             this.current_page = parseInt(current_page)
#             this.controller = attrs.ngController.toLowerCase().slice(0, -5)
#             this.loadPage()
#
#         this.loadPage = ->
#             this.Resource.get
#                 page: this.current_page
#             , (response) =>
#                 this.page = response
#                 $rootScope.frontend_loading = false
#
#         this.pageChanged = ->
#             $rootScope.frontend_loading = true
#             this.loadPage()
#             this.changeUrl()
#
#         # change browser user / history push
#         this.changeUrl = ->
#             window.history.pushState('', '', this.controller + '?page=' + this.current_page)
#
#         this
#     .service 'FormService', ($rootScope, $q, $timeout) ->
#         this.init = (Resource, id, model) ->
#             this.dataLoaded = $q.defer()
#             $rootScope.frontend_loading = true
#             this.Resource = Resource
#             this.saving = false
#             if id
#                 this.model = Resource.get({id: id}, => modelLoaded())
#             else
#                 this.model = new Resource()
#                 modelLoaded()
#
#
#         modelLoaded = =>
#             $rootScope.frontend_loading = false
#             $timeout =>
#                 this.dataLoaded.resolve(true)
#
#         beforeSave = =>
#             ajaxStart()
#             this.beforeSave() if this.beforeSave isnt undefined
#             this.saving = true
#
#         this.edit = ->
#             beforeSave()
#             this.model.$update().then =>
#                 this.saving = false
#                 ajaxEnd()
#
#         this.create = ->
#             beforeSave()
#             this.model.$save().then (response) =>
#                 # вырезаем MODEL из url типа /website/model/create
#                 l = window.location.pathname.split('/')
#                 redirect l[l.length - 2] + "/#{response.id}/edit"
#
#         this
