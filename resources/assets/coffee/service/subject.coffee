angular.module 'Egerep'
    .service 'SubjectService', ->
        # пары предметов
        this.pairs = [
            [1, 2]
            [3, 4]
            [6, 7]
            [8, 9]
        ]

        this.init = (subjects) ->
            this.subjects = subjects

        this.pairsControl = (subject_id) ->
            if subject_id
                angular.forEach this.subjects, (enabled, id) =>
                    pair = _.filter this.pairs, (p) ->
                        p.indexOf(parseInt(subject_id)) isnt -1
                    pair.push([subject_id]) if not pair.length
                    this.subjects[id] = false if pair[0].indexOf(parseInt(id)) is -1

        this.selected = (subject_id) ->
            this.subjects isnt undefined and this.subjects[subject_id] isnt undefined and this.subjects[subject_id]

        this.select = (subject_id) ->
            console.log 'here'
            this.subjects[subject_id] = if this.subjects[subject_id] then not this.subjects[subject_id] else true
            this.pairsControl(subject_id)

        this.getSelected = ->
            ids = []
            angular.forEach this.subjects, (enabled, id) ->
                ids.push(id) if enabled
            ids

        this.opacityControl = (id) ->
            return false if not this.getSelected().length
            selected_id = parseInt(this.getSelected()[0])
            pair = _.filter this.pairs, (p) ->
                p.indexOf(selected_id) isnt -1
            if not pair.length
                selected_id != id
            else
                pair[0].indexOf(parseInt(id)) is -1

        this
