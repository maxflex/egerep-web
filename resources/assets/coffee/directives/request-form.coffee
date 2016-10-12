angular.module('Egerep')
    .directive 'requestForm', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
        templateUrl: 'directives/request-form'
        controller: ($scope, $element, $timeout, Request) ->
            # отправить заявку
            $scope.request = ->
                if checkForm()
                    $scope.tutor.request.tutor_id = $scope.tutor.id
                    Request.save $scope.tutor.request, ->
                        $scope.tutor.request_sent = true
                        if $scope.sentIds isnt undefined then $scope.sentIds.push($scope.tutor.id)

            # проверить перед отправкой форму
            checkForm = ->
                phone_element = $($element).find('.phone-field')

                # номер телефона не заполнен полностью
                if not isFull(phone_element.val())
                    phone_element.focus().notify 'номер телефона не заполнен полностью', notify_options
                    return false

                # номер должен начинаться с 9 или 4
                phone_number = phone_element.val().match(/\d/g).join('')
                if phone_number[1] != '4' and phone_number[1] != '9'
                    phone_element.focus().notify 'номер должен начинаться с 9 или 4', notify_options
                    return false
                true

            # номер телефона полный
            isFull = (number) ->
                return false if number is undefined or number is ""
                !number.match(/_/)

            notify_options =
                hideAnimation: 'fadeOut'
                showDuration: 0
                hideDuration: 400
                autoHideDelay: 3000
