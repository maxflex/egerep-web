angular.module('Egerep')
    .controller 'LoginCtrl', ($scope, Sms, Tutor) ->
        bindArguments($scope, arguments)

        login = ->
            Tutor.login {}, (response) ->
                $scope.tutor = response
            , ->
                $scope.tutor = null

        login()

        $scope.sendCode = ->
            $scope.error_message = false
            Sms.save
                phone: $scope.phone
            , ->
                $scope.code_sent = true
            , ->
                $scope.error_message = 'неверный номер телефона'

        $scope.checkCode = ->
            $scope.error_message = false
            Sms.get
                code: $scope.code
            , ->
                login()
            , (response) ->
                redirect('/') if response.status == 403
                $scope.error_message = 'код введен неверно'
