angular
.module 'Egerep'
.controller 'Index', ($scope, Tutor) ->
	bindArguments($scope, arguments)

	$scope.getSubjects = (tutor) ->
		if 'string' is typeof tutor.subjects
			subject_ids = tutor.subjects.split(',').map (id)->
				+id
		else
			subject_ids = tutor.subjects

		_.filter $scope.subjects, (subject) ->
			subject_ids.indexOf(subject.id) != -1
