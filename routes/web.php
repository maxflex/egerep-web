<?php

    # Templates for angular directives
    Route::get('directives/{directive}', function($directive) {
        return view("directives.{$directive}");
    });

    # Tutor profile page
    Route::get('tutor/{id}', 'PagesController@tutor');

    # MAIN ALL PAGES ROUTE
    Route::get('{url?}', 'PagesController@index');
