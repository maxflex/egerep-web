Краткая анкета {{$tutor->first_name}} {{$tutor->middle_name}} - репетитора по {{
    implode(', ', array_map(function($subject_id) {
        return dbFactory('subjects')->whereId($subject_id)->value('dative');
    }, $tutor->subjects))
}}: возраст {{ yearsPassed($tutor->birth_year) }} лет, педагогический стаж {{ yearsPassed($tutor->start_career_year) }} лет, место проведения занятий –
@if(count($tutor->markers))
    @foreach($tutor->markers as $index => $marker)
        м. {{ $marker->metros[0]->station->title }}{{ $loop->last ? '' : ', ' }}
    @endforeach
@else
    только у ученика
@endif
, стоимость занятия {{ $tutor->public_price }} рублей/{{ $tutor->lesson_duration }} минут, средняя оценка {{ $tutor->review_avg }} и {{ $tutor->reviews_count }} отзывов
