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
                if PhoneService.checkForm($element)
                    $scope.tutor.request.tutor_id = $scope.tutor.id
                    Request.save $scope.tutor.request, ->
                        $scope.tutor.request_sent = true
                        if $scope.sentIds isnt undefined then $scope.sentIds.push($scope.tutor.id)
                    , ->
                        $scope.tutor.request_error = true
