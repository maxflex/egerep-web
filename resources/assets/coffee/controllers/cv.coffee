angular
    .module 'Egerep'
    .controller 'Cv', ($scope, $timeout, Tutor, FileUploader, Cv, PhoneService, StreamService) ->
        bindArguments($scope, arguments)

        $scope.error_text = 'ошибка: максимальная длина текста – 1000 символов'

        $timeout ->
            console.log 1
            # $('.digits-only').inputmask("Regex", {regex: "[0-9]*"})
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
                    $scope.application.filename = response.filename
                    $scope.application.filesize = response.size
                else
                    $scope.upload_error = true
                    $scope.application.filename = undefined

        $scope.clearFile = ->
            $scope.upload_error = false
            $scope.application.filename = undefined

        $scope.upload = (e) ->
            e.preventDefault()
            $scope.upload_error = false
            $('#upload-button').trigger 'click'
            false

        $scope.sendApplication = ->
            # if PhoneService.checkForm($('.phone-input'))
            Cv.save $scope.application, ->
                $scope.application.sent = true
            , (response) ->
                if response.status is 422
                    $scope.errors = {}
                    angular.forEach response.data, (errors, field) ->
                        $scope.errors[field] = errors
                        selector = "[ng-model$='#{field}']"
                        $("input#{selector}, textarea#{selector}").focus()
                else
                    $scope.application.error = true
