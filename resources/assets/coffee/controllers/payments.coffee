angular
    .module 'Egerep'
    .controller 'Payments', ($scope, $timeout, $http, StreamService) ->
        bindArguments($scope, arguments)

        $scope.loading = false
        $scope.initial_loading = true

        $scope.sum = ''
        $scope.fio = ''
        $scope.error = ''

        $scope.proceed = ->
            $scope.error = ''
            $scope.loading = true
            $http.post('api/payments', {sum: $scope.sum, fio: $scope.fio}).then (r) ->
                redirect(r.data.formUrl)
            , (e) ->
                $scope.error = e.data[Object.keys(e.data)[0]][0]
                $scope.loading = false
        
        $timeout ->
            if $scope.orderId
                $http.post('api/payments/getOrderStatus', {
                    orderId: $scope.orderId,
                    lang: $scope.lang,
                }).then (r) ->
                    $scope.orderStatus = r.data
                    $scope.initial_loading = false
            else
                $scope.initial_loading = false