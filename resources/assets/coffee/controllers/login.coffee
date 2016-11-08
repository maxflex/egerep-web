angular.module('Egerep')
    .controller 'LoginCtrl', ($scope, Sms) ->
        $scope.sendCode = ->
            $scope.phone_error = false
            Sms.save
                phone: $scope.phone
            , ->
                $scope.code_sent = true
            , ->
                $scope.phone_error = true

        $scope.checkCode = ->
            Sms.get
                code: $scope.code
