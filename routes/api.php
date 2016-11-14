<?php
URL::forceSchema('https');
Route::group(['namespace' => 'Api', 'as' => 'api.'], function () {
    Route::post('tutors/search', 'TutorsController@search');
    Route::get('tutors/login', 'TutorsController@login');
    Route::get('tutors/iteraction/{id}/{type}', 'TutorsController@iteraction');
    Route::get('tutors/reviews/{id}', 'TutorsController@reviews');

    Route::get('reviews/random', 'RandomReviewsController@index');

    Route::resource('requests', 'RequestsController', ['only' => 'store']);

    Route::post('cv/uploadPhoto', 'CvController@uploadPhoto');
    Route::resource('cv', 'CvController', ['only' => 'store']);

    Route::resource('sms', 'SmsController');
});
