angular
    .module 'Egerep'
    .controller 'Cv', ($scope, Tutor, FileUploader, Cv, PhoneService) ->
        bindArguments($scope, arguments)

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
            onCompleteItem: (i, response, status) ->
                if status is 200
                    $scope.application.filename = response
                else
                    $('.upload-photo-link').notify 'ошибка загрузки файла', notify_options
                    $scope.application.filename = null

        # $scope.uploader.filters.push
        #     name: 'imageFilter',
        #     fn: (item, options) ->
        #         type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
        #         return '|jpg|png|jpeg|bmp|gif|svg|'.indexOf(type) isnt -1

        $scope.upload = (e) ->
            e.preventDefault()
            $('#upload-button').trigger 'click'
            false

        $scope.sendApplication = ->
            if PhoneService.checkForm $('.phone-input')
                Cv.save $scope.application, ->
                    $scope.application.sent = true