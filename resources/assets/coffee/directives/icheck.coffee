angular.module('Egerep').directive 'icheck', ($timeout, $parse) ->
  {
    require: 'ngModel'
    link: ($scope, element, $attrs, ngModel) ->
      $timeout ->
        value = undefined
        value = $attrs['value']
        $scope.$watch $attrs['ngModel'], (newValue) ->
          $(element).iCheck 'update'
          return
        $(element).iCheck(
          checkboxClass: 'custom-checkbox',
          radioClass: 'custom-radio',
          checkedClass: 'checked',
          cursor: true).on 'ifChanged', (event) ->
          if $(element).attr('type') == 'checkbox' and $attrs['ngModel']
            $scope.$apply ->
              ngModel.$setViewValue event.target.checked
          if $(element).attr('type') == 'radio' and $attrs['ngModel']
            return $scope.$apply(->
              ngModel.$setViewValue value
            )
          return

  }
