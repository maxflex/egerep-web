angular.module 'Egerep'
    .service 'StepperService', ->
        this.questions = [
            'В каком классе учится ребенок?',
            'Выберите направление подготовки',
            'Укажите предмет',
            'Укажите место для занятий',
            'Укажите сортировку'
        ]

        this.current_step = 0

        # первый запуск
        this.startStepper = ->
            openStepper()
            $.get('/open-stepper')

        this.next = ->
            this.current_step++

        this.back = ->
            this.current_step--

        this.grades = ['1 класс', '2 класс', '3 класс', '4 класс', '5 класс', '6 класс', '7 класс', '8 класс', '9 класс', '10 класс', '11 класс', 'студент колледжа', 'студент вуза', 'взрослый']

        this.preparation_directions = ['Подготовка к ЕГЭ', 'Подготовка к ОГЭ']

        this
