angular.module 'Egerep'
    .directive 'plural', ->
        restrict: 'E'
        scope:
            count: '='      # кол-во
            type: '@'       # тип plural age | student | ...
            noneText: '@'   # текст, если кол-во равно нулю
        templateUrl: '/directives/plural'
        controller: ($scope, $element, $attrs, $timeout) ->
            $scope.textOnly = $attrs.hasOwnProperty('textOnly')
            $scope.hideZero = $attrs.hasOwnProperty('hideZero')

            $scope.when =
                'age': ['год', 'года', 'лет']
                'month': ['месяц', 'месяца', 'месяцев']
                'student': ['ученик', 'ученика', 'учеников']
                'minute': ['минуту', 'минуты', 'минут']
                'hour': ['час', 'часа', 'часов']
                'day': ['день', 'дня', 'дней']
                'meeting': ['встреча', 'встречи', 'встреч']
                'score': ['балл', 'балла', 'баллов']
                'rubbles': ['рубль', 'рубля', 'рублей']
                'lesson': ['занятие', 'занятия', 'занятий']
                'client': ['клиент', 'клиента', 'клиентов']
                'mark': ['оценки', 'оценок', 'оценок']
                'marks': ['оценка', 'оценки', 'оценок']
                'review': ['отзыв', 'отзыва', 'отзывов']
                'request': ['заявка', 'заявки', 'заявок']
                'station': ['станцию', 'станции', 'станций']
                'tutor': ['репетитор', 'репетитора', 'репетиторов']
                'profile': ['анкета', 'анкеты', 'анкет']
                'schooler': ['школьник нашел', 'школьника нашли', 'школьников нашли']
                'taught': ['подготовлен', 'подготовлено', 'подготовлено']
                'address': ['адрес', 'адреса', 'адресов']
