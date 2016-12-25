angular.module('Egerep')
    .directive 'requestForm', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
            index: '='
        templateUrl: 'directives/request-form'
        controller: ($scope, $element, $timeout, Request, Sources) ->
            # отправить заявку
            $scope.request = ->
                $scope.tutor.request = {} if $scope.tutor.request is undefined
                $scope.tutor.request.tutor_id = $scope.tutor.id
                Request.save $scope.tutor.request, ->
                    $scope.tutor.request_sent = true
                    $scope.$parent.StreamService.run(
                        identifySource(),
                        if $scope.index then ($scope.index + 1) else null
                    )
                    trackDataLayer()
                , (response) ->
                    if response.status is 422
                        angular.forEach response.data, (errors, field) ->
                            selector = "[ng-model$='#{field}']"
                            $($element).find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                    else
                        $scope.tutor.request_error = true

            identifySource = ->
                if $scope.tutor.id
                    if $scope.index then Sources.SERP_REQUEST else Sources.PROFILE_REQUEST
                else
                    Sources.HELP_REQUEST


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
