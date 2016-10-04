<?php

    # Templates for angular directives
    Route::get('directives/{directive}', function($directive) {
        return view("directives.{$directive}");
    });

    # Tutor profile page
    Route::get('{id}', 'PagesController@tutor')->where('id', '[0-9]+');;

    # MAIN ALL PAGES ROUTE
    Route::get('{url?}', 'PagesController@index');
