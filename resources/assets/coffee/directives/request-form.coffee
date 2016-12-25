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
            $timeout ->
                if $scope.index isnt undefined
                    $scope.index++
                else
                    $scope.index = if window.location.hash then window.location.hash.substring(1) else null
            , 500

            # отправить заявку
            $scope.request = ->
                $scope.tutor.request = {} if $scope.tutor.request is undefined
                $scope.tutor.request.tutor_id = $scope.tutor.id
                Request.save $scope.tutor.request, ->
                    $scope.tutor.request_sent = true
                    $scope.$parent.StreamService.run(identifySource(), $scope.index)
                    trackDataLayer()
                , (response) ->
                    if response.status is 422
                        angular.forEach response.data, (errors, field) ->
                            selector = "[ng-model$='#{field}']"
                            $($element).find("input#{selector}, textarea#{selector}").focus().notify errors[0], notify_options
                    else
                        $scope.tutor.request_error = true

            profilePage = -> RegExp(/^\/[\d]+$/).test(window.location.pathname)

            identifySource = ->
                if $scope.tutor.id
                    if profilePage() then Sources.PROFILE_REQUEST else Sources.SERP_REQUEST
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
                                id: googleClientId()
                                affiliation: 'serp' # @todo: profile|request
                                revenue: $scope.tutor.public_price
                            products: [
                                id: $scope.tutor.id
                                price: $scope.tutor.public_price
                                brand: $scope.tutor.subjects
                                category: $scope.tutor.gender + '_' + $rootScope.yearsPassed($scope.tutor.birth_year) # пол_возраст
                                quantity: 1
                            ]
