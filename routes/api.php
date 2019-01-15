<?php
if (App::environment('production')) {
    URL::forceSchema('https');
}

Route::group(['namespace' => 'Api', 'as' => 'api.'], function () {
    Route::post('tutors/search', 'TutorsController@search');
    Route::get('tutors/login', 'TutorsController@login');
    Route::post('tutors/markers', 'TutorsController@markers');
    Route::get('tutors/reviews/{id}', 'TutorsController@reviews');

    Route::post('payments/getOrderStatus', 'PaymentsController@getOrderStatus');
    Route::post('payments', 'PaymentsController@proceed');
    
    Route::resource('tutors', 'TutorsController');

    Route::get('reviews/random', 'RandomReviewsController@index');
    Route::get('reviews', 'ReviewsController@index');

    Route::resource('requests', 'RequestsController', ['only' => 'store']);

    Route::post('cv/uploadPhoto', 'CvController@uploadPhoto');
    Route::resource('cv', 'CvController', ['only' => 'store']);
    Route::resource('stream', 'StreamController', ['only' => 'store']);

    Route::resource('sms', 'SmsController');
});