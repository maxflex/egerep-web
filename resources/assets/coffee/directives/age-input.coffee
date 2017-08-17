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
                    if scope.ngModel
                        if scope.prefix is 'до'
                            $('.age-field').removeClass('has-error')
                            $('.age-field').addClass('has-error') if parseInt(scope.$parent.search.age_from.replace(/\D/g,'')) > parseInt(scope.ngModel)
                        updateModel(scope.prefix + ' ' + scope.ngModel + ' лет')
                .on 'keyup', -> updateModel(scope.ngModel.replace(/\D/g,''))
                .on 'focus', -> updateModel(scope.ngModel.replace(/\D/g,''))

