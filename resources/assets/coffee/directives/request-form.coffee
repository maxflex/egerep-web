angular.module('Egerep')
    .directive 'requestForm', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
            index: '='
        templateUrl: (elem, attrs) ->
            if attrs.hasOwnProperty('mobile') then 'directives/request-form-mobile' else 'directives/request-form'
        controller: ($scope, $element, $timeout, $rootScope, Request, Sources) ->
            # отправить заявку
            $scope.request = ->
                $scope.tutor = {} if not $scope.tutor
                $scope.tutor.request = {} if $scope.tutor.request is undefined
                $scope.tutor.request.tutor_id = $scope.tutor.id
                Request.save $scope.tutor.request, ->
                    $scope.tutor.request_sent = true
                    $scope.$parent.StreamService.run 'request', $scope.$parent.StreamService.identifySource($scope.tutor),
                        position: $scope.index or $scope.$parent.index
                        tutor_id: $scope.tutor.id
                    trackDataLayer()
                , (response) ->
                    if response.status is 422
                        angular.forEach response.data, (errors, field) ->
                            selector = "[ng-model$='#{field}']"
                            $($element).find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                    else
                        $scope.tutor.request_error = true

            trackDataLayer = ->
                dataLayerPush
                    event: 'purchase'
                    ecommerce:
                        currencyCode: 'RUR'
                        purchase:
                            actionField:
                                id: googleClientId()
                                affiliation: $scope.$parent.StreamService.identifySource()
                                revenue: $scope.tutor.public_price
                            products: [
                                id: $scope.tutor.id
                                price: $scope.tutor.public_price
                                brand: $scope.tutor.subjects
                                category: $scope.tutor.gender + '_' + $rootScope.yearsPassed($scope.tutor.birth_year) # пол_возраст
                                quantity: 1
                            ]
