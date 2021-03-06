angular.module('Egerep')
    .directive 'requestForm', ->
        replace: true
        scope:
            tutor: '='
            sentIds: '='
            index: '='
            source: '@'
        templateUrl: (elem, attrs) ->
            if attrs.hasOwnProperty('mobile') then 'directives/request-form-mobile' else 'directives/request-form'
        controller: ($scope, $element, $attrs, $timeout, $rootScope, Request, Sources) ->
            $scope.fixedHeight = $attrs.hasOwnProperty('fixedHeight')

            # отправить заявку
            $scope.request = ->
                $scope.tutor = {} if not $scope.tutor
                $scope.tutor.request = {} if $scope.tutor.request is undefined
                $scope.tutor.request.tutor_id = $scope.tutor.id
                Request.save $scope.tutor.request, ->
                    $scope.tutor.request_sent = true
                    $scope.$parent.StreamService.run 'request', $scope.source || $scope.$parent.StreamService.identifySource($scope.tutor),
                        position: $scope.index or $scope.$parent.index
                        tutor_id: $scope.tutor.id
                    trackDataLayer()
                , (response) ->
                    if response.status is 422
                        angular.forEach response.data, (errors, field) ->
                            selector = "[ng-model$='#{field}']"
                            # $('html,body').animate({scrollTop: $("input#{selector}, textarea#{selector}").first().offset().top}, 0)
                            $($element).find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                    else
                        $scope.tutor.request_error = true

            trackDataLayer = ->
                dataLayerPush
                    event: 'purchase'
                    ecommerce:
                        currencyCode: 'RUB'
                        purchase:
                            actionField:
                                id: googleClientId()
                                affiliation: $scope.$parent.StreamService.identifySource()
                                revenue: $scope.tutor.public_price
                            products: [
                                id: $scope.tutor.id
                                price: $scope.tutor.public_price
                                brand: if $scope.tutor.subjects then $scope.tutor.subjects.join(',') else null
                                category: $scope.tutor.gender + '_' + $scope.tutor.age # пол_возраст
                                quantity: 1
                            ]
