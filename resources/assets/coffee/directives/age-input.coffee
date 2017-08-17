angular.module('Egerep')
    .directive 'ngAge', ->
        restrict: 'A'
        require: 'ngModel'
        scope:
            ngModel: '='
            prefix: '@'
        link: (scope, element, attrs, ngModel) ->
            updateModel = (value) ->
                ngModel.$setViewValue(value)
                ngModel.$render()

            $(element)
                .on 'blur', ->
                    $('.age-field').removeClass('has-error')
                    age_to = scope.$parent.search.age_to
                    age_from = scope.$parent.search.age_from
                    if (age_to && age_from)
                        $('.age-field').addClass('has-error') if getNumber(age_from) > getNumber(age_to)
                    updateModel(scope.prefix + ' ' + scope.ngModel + ' лет') if scope.ngModel
                .on 'keyup', -> updateModel(getNumber(scope.ngModel))
                .on 'focus', -> updateModel(getNumber(scope.ngModel))

