angular.module 'Egerep'
    .service 'StepperService', ->

        this.questions = [
            'В каком классе учится ребенок?',
            'Укажите предмет',
            'Выберите направление подготовки',
            'Укажите место для занятий',
            ['Укажите ближайшее к вам метро', 'Укажите метро, куда должен выезжать репетитор'],
            'Укажите сортировку анкет'
        ]

        this.current_step = 0
        this.max_step = 0

        # первый запуск
        this.startStepper = ->
            openStepper()
            $.get('/open-stepper')

        this.next = ->
            if this.current_step == this.questions.length - 1
                scope.stepperFilter()
                return
            this.current_step++
            this.max_step = this.current_step if this.max_step < this.current_step

        this.back = ->
            this.current_step--

        this.goToStep = (index) ->
            this.current_step = index if index <= this.max_step

        this.nextStepDisabled = (step = null) ->
            step = this.current_step if step is null
            return true if step == 0 && !scope.search.grade
            return true if step == 1 && !scope.SubjectService.getSelected().length
            return true if step == 2 && !scope.search.preparation_direction
            return true if step == 3 && !scope.search.place
            return true if step == 4 && !scope.search.station_id
            return true if step == 5 && !scope.search.sort

        this.grades = ['1 класс', '2 класс', '3 класс', '4 класс', '5 класс', '6 класс', '7 класс', '8 класс', '9 класс', '10 класс', '11 класс', 'студент колледжа', 'студент вуза', 'взрослый']

        this.places = [
            {value: 'tutor', title: 'дома у репетитора'},
            {value: 'home', title: 'дома у ученика'}
        ]

        this.sort = [
            {value: 'most-popular', title: 'сначала самые востребованные по Москве'},
            {value: 'nearest-metro', title: 'ближайшие к метро'}
        ]

        this.preparation_directions = ['подготовка к ЕГЭ', 'подготовка к ОГЭ', 'подготовка к ДВИ', 'улучшение успеваемости в школе']

        this

# математика физика химия биология русский литература
# обществознание история английский информатика гео
