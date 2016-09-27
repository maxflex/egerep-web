<?php

Route::group(['namespace' => 'Api'], function () {
    Route::post('tutors/search', 'TutorsController@search');
    Route::get('tutors/view/{id}', 'TutorsController@view');
    Route::get('tutors/reviews/{id}', 'TutorsController@reviews');
    Route::resource('requests', 'RequestsController', ['only' => 'store']);
});
