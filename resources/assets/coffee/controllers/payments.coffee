angular
    .module 'Egerep'
    .controller 'Payments', ($scope, $timeout, $http, StreamService) ->
        bindArguments($scope, arguments)

        $scope.loading = false
        $scope.initial_loading = true

        $scope.proceed = ->
            $scope.loading = true
            $http.post('api/payments').then (r) ->
                redirect(r.data.formUrl)
        
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