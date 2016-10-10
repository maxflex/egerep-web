<span ng-repeat="subject_id in subjectIds track by $index">@{{ subjects[subject_id].dative }}@{{ $last ? '' : ($index + 2 == subjectIds.length) ? ' и ' : ', ' }}</span>
