angular.module('Egerep')
    .controller 'LoginCtrl', ($scope, $timeout, Sms, Tutor, StreamService) ->
        bindArguments($scope, arguments)
        
        $scope.loading = true

        login = ->
            Tutor.login {}, (response) ->
                $scope.tutor = response
                redirect('/payment')
            , ->
                $scope.tutor = null
                $scope.loading = false


        login()

        $scope.sendCode = ->
            $scope.error_message = false
            $scope.loading = true
            Sms.save
                phone: $scope.phone
            , ->
                $scope.code_sent = true
                $timeout -> $('#code-input').focus()
                $scope.loading = false
            , ->
                $scope.error_message = 'неверный номер телефона'
                $scope.loading = false

        $scope.checkCode = ->
            $scope.error_message = false
            $scope.loading = true
            Sms.get
                code: $scope.code
            , ->
                login()
            , (response) ->
                redirect('/') if response.status == 403
                $scope.error_message = 'код введен неверно'
                $scope.loading = false
