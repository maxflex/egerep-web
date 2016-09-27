<?php

    # Templates for angular directives
    Route::get('directives/{directive}', function($directive) {
        return view("directives.{$directive}");
    });

    # MAIN ALL PAGES ROUTE
    Route::get('{url?}', 'PagesController@index');
