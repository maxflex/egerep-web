angular.module 'Egerep'
    .service 'PhoneService', ->
        notify_options =
            hideAnimation: 'fadeOut'
            showDuration: 0
            hideDuration: 400
            autoHideDelay: 3000

        # проверить перед отправкой форму
        this.checkForm = (element)->
            phone_element = $(element).find('.phone-field')

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

        this
