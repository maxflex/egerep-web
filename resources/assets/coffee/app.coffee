angular.module("Egerep", ['ngSanitize', 'ngResource', 'ngMaterial', 'ngAnimate', 'ui.sortable', 'ui.bootstrap', 'angular-ladda', 'angular-inview'])
    .config [
        '$compileProvider'
        ($compileProvider) ->
            $compileProvider.aHrefSanitizationWhitelist /^\s*(https?|ftp|mailto|chrome-extension|sip):/
	]
    .config (laddaProvider) ->
        laddaProvider.setOption
            spinnerColor: '#83b060'
    .filter 'cut', ->
      (value, wordwise, max, nothing = '', tail) ->
        if !value
          return nothing
        max = parseInt(max, 10)
        if !max
          return value
        if value.length <= max
          return value
        value = value.substr(0, max)
        if wordwise
          lastspace = value.lastIndexOf(' ')
          if lastspace != -1
            #Also remove . and , so its gives a cleaner result.
            if value.charAt(lastspace - 1) == '.' or value.charAt(lastspace - 1) == ','
              lastspace = lastspace - 1
            value = value.substr(0, lastspace)
        value + (tail or '…')
    .filter 'hideZero', ->
        (item) ->
            if item > 0 then item else null
    .run ($rootScope, $q) ->
        # отвечает за загрузку данных
        $rootScope.dataLoaded = $q.defer()
        # конец анимации front-end загрузки и rebind маск
        $rootScope.frontendStop = (rebind_masks = true) ->
            $rootScope.frontend_loading = false
            $rootScope.dataLoaded.resolve(true)
            rebindMasks() if rebind_masks

        $rootScope.range = (min, max, step) ->
          step = step or 1
          input = []
          i = min
          while i <= max
            input.push i
            i += step
          input

          # skip_values – какие значения в enum пропускать
          # allowed_user_ids – пользователи, которым разрешено выбирать значения
          # recursion – функция была запущена рекурсивно (внизу)
        $rootScope.toggleEnum = (ngModel, status, ngEnum, skip_values = [], allowed_user_ids = [], recursion = false) ->
            # если установлено значение, которое пропускается для обычных пользователей,
            # то запрещать его смену
            return if not recursion and parseInt(ngModel[status]) in skip_values and $rootScope.$$childHead.user.id not in allowed_user_ids

            statuses = Object.keys(ngEnum)
            status_id = statuses.indexOf ngModel[status].toString()
            status_id++
            status_id = 0 if status_id > (statuses.length - 1)
            ngModel[status] = statuses[status_id]
            # if in skip_values
            $rootScope.toggleEnum(ngModel, status, ngEnum, skip_values, allowed_user_ids, true) if status_id in skip_values and $rootScope.$$childHead.user.id not in allowed_user_ids

        # обновить + ждать ответа от сервера
        $rootScope.toggleEnumServer = (ngModel, status, ngEnum, Resource) ->
            statuses = Object.keys(ngEnum)
            status_id = statuses.indexOf ngModel[status].toString()
            status_id++
            status_id = 0 if status_id > (statuses.length - 1)

            update_data = {id: ngModel.id}
            update_data[status] = status_id

            Resource.update update_data, ->
                ngModel[status] = statuses[status_id]

        $rootScope.formatDateTime = (date) ->
            moment(date).format "DD.MM.YY в HH:mm"

        $rootScope.formatDate = (date, full_year = false) ->
            return '' if not date
            moment(date).format "DD.MM.YY" + (if full_year then "YY" else "")

        $rootScope.dialog = (id) ->
            $("##{id}").modal 'show'
            return

        $rootScope.closeDialog = (id) ->
            $("##{id}").modal 'hide'
            return

        $rootScope.ajaxStart = ->
            ajaxStart()
            $rootScope.saving = true

        $rootScope.ajaxEnd = ->
            ajaxEnd()
            $rootScope.saving = false

        $rootScope.findById = (object, id) ->
            _.findWhere(object, {id: parseInt(id)})

        # prop2 – второй уровень вложенности
        $rootScope.total = (array, prop, prop2 = false) ->
            sum = 0
            $.each array, (index, value) ->
                v = value[prop]
                v = v[prop2] if prop2
                sum += v
            sum

        # сколько лет прошло с указанной даты
        $rootScope.yearsPassed = (year) ->
            moment().format("YYYY") - year

        $rootScope.deny = (ngModel, prop) ->
            ngModel[prop] = +(!ngModel[prop])

        $rootScope.formatBytes = (bytes) ->
          if bytes < 1024
            bytes + ' Bytes'
          else if bytes < 1048576
            (bytes / 1024).toFixed(1) + ' KB'
          else if bytes < 1073741824
            (bytes / 1048576).toFixed(1) + ' MB'
          else
            (bytes / 1073741824).toFixed(1) + ' GB'
