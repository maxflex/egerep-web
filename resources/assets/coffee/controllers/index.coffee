angular
    .module 'Egerep'
    .controller 'Index', ($scope, Tutor) ->
        bindArguments($scope, arguments)
        # сотрудничает с 12 сентября 2000 года
        $scope.dateToText = (date) ->
            text_date = moment(date).format 'DD MMMM YYYY'
            # вырезаем дату, оставляем месяц и год
            # нужно именно так, чтобы осталось правильное склонение месяца
            text_date.substr(3)
