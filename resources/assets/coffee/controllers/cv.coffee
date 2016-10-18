angular
    .module 'Egerep'
    .controller 'Cv', ($scope, $timeout, Tutor, FileUploader, Cv, PhoneService) ->
        bindArguments($scope, arguments)

        $scope.error_text = 'ошибка: максимальная длина текста – 1000 символов'

        $timeout ->
            $('.name-field').inputmask("Regex", {regex: "[A-Za-zа-яА-Я- ]*"})
            $('textarea').inputmask("Regex", {regex: "[0-9A-Za-zа-яА-Я()-., ]*"})
            $('.digits-only').inputmask("Regex", {regex: "[0-9]*"})
        , 1000

        $scope.application =
            agree_to_publish: 1

        FileUploader.FileSelect.prototype.isEmptyAfterSelection = ->
            true

        $scope.uploader = new FileUploader
            url: 'api/cv/uploadPhoto'
            alias: 'photo'
            autoUpload: true
            method: 'post'
            removeAfterUpload: true
            onProgressItem: (i, progress) ->
                $scope.percentage = progress
            onCompleteItem: (i, response, status) ->
                $scope.percentage = undefined
                if status is 200
                    $scope.application.filename = response
                else
                    $scope.upload_error = true
                    $scope.application.filename = undefined

        $scope.clearFile = ->
            $scope.upload_error = false
            $scope.application.filename = undefined
        # $scope.uploader.filters.push
        #     name: 'imageFilter',
        #     fn: (item, options) ->
        #         type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
        #         return '|jpg|png|jpeg|bmp|gif|svg|'.indexOf(type) isnt -1

        $scope.upload = (e) ->
            e.preventDefault()
            $scope.upload_error = false
            $('#upload-button').trigger 'click'
            false

        $scope.sendApplication = ->
            if PhoneService.checkForm($('.phone-input')) and checkForm()
                Cv.save $scope.application, ->
                    $scope.application.sent = true
                , ->
                    $scope.application.error = true

        checkForm = ->
            valid = true
            $scope.errors = {}

            $('.name-field').each (index, element) ->
                if $(element).val().length > 60
                    valid = false
                    $(element).focus().notify 'ошибка: максимальная длина текста – 60 символов', notify_options
                    return

            $('textarea').each (index, element) ->
                if $(element).val().length > 1000
                    valid = false
                    model = $(element).focus().attr('ng-model').split('.')[1]
                    $scope.errors[model] = true
                    return

            valid
