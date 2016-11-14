angular.module('Egerep')
    .directive 'requestForm', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
        templateUrl: 'directives/request-form'
        controller: ($scope, $element, $timeout, Request, PhoneService) ->
            # отправить заявку
            $scope.request = ->
                $scope.tutor.request = {} if $scope.tutor.request is undefined
                $scope.tutor.request.tutor_id = $scope.tutor.id
                Request.save $scope.tutor.request, ->
                    $scope.tutor.request_sent = true
                    trackDataLayer()
                , (response) ->
                    if response.status is 422
                        angular.forEach response.data, (errors, field) ->
                            selector = "[ng-model$='#{field}']"
                            $($element).find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                    else
                        $scope.tutor.request_error = true

            trackDataLayer = ->
                window.dataLayer = window.dataLayer || []
                window.dataLayer.push
                    event: 'purchase'
                    ecommerce:
                        currencyCode: 'RUR'
                        purchase:
                            actionField:
                                id: $scope.tutor.id
                                affilaction: 'serp' # @todo: profile|request
                                revenue: $scope.tutor.public_price
                            products: [
                                id: $scope.tutor.id
                                price: $scope.tutor.public_price
                                brand: $scope.tutor.subjects
                                category: $scope.tutor.markers
                                quantity: 1
                            ]
