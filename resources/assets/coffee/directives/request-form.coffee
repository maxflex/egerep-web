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
                if PhoneService.checkForm($element) && checkLength('request-name', 60) && checkLength('request-comment', 1000)
                    $scope.tutor.request.tutor_id = $scope.tutor.id
                    Request.save $scope.tutor.request, ->
                        $scope.tutor.request_sent = true
                        # if $scope.sentIds isnt undefined then $scope.sentIds.push($scope.tutor.id)
                    , ->
                        $scope.tutor.request_error = true

            checkLength = (element_class, max_length) ->
                el = $($element).find('.' + element_class)
                if el.val().length > max_length
                    el.notify "ошибка: максимальная длина текста - #{max_length} символов", notify_options
                    return false
                true

            $timeout ->
                $($element).find('input:not(.phone-field), textarea').inputmask("Regex", {regex: "[0-9A-Za-zа-яА-Я()-., ]*"})
