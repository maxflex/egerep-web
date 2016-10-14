angular
    .module 'Egerep'
    .controller 'Cv', ($scope, Tutor, FileUploader, Cv) ->
        bindArguments($scope, arguments)

        FileUploader.FileSelect.prototype.isEmptyAfterSelection = ->
            true

        $scope.uploader = new FileUploader
            url: '/cv/uploadPhoto'
            alias: 'cv'
            autoUpload: true
            method: 'post'
            removeAfterUpload: true
            onCompleteItem: (i, response, status) ->
                notifySuccess 'Импортирован' if status is 200
                notifyError 'Ошибка!' if status isnt 200
            onWhenAddingFileFailed  = (item, filter, options) ->
                if filter.name is "queueLimit"
                    this.clearQueue()
                    this.addToQueue(item)

        $scope.uploader.filters.push
            name: 'imageFilter',
            fn: (item, options) ->
                type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|png|jpeg|bmp|gif|svg|'.indexOf(type) isnt -1

        $scope.upload = (e) ->
            e.preventDefault()
            $('#upload-button').trigger 'click'
            false

        $scope.sendApplication = ->
            Cv.save $scope.application, ->
                $scope.application.sent = true
