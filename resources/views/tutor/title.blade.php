{{$tutor->first_name}} {{$tutor->middle_name}} – репетитор по {{
    implode(', ', array_map(function($subject_id) {
        return dbFactory('subjects')->whereId($subject_id)->value('dative');
    }, $tutor->subjects)) }}
