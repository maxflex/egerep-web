<?php
    use App\Models\Variable;

    URL::forceSchema('https');

    Route::get('/', function() {
        return Variable::display(isMobile() ? 'page-index-mobile' : 'page-index');
    });
    Route::get('/request', function() {
        return Variable::display('page-tutor-request');
    });
    Route::get('/cv', function() {
        return Variable::display('page-cv');
    });
    Route::get('/login', function() {
        return Variable::display('page-login');
    });

    # Templates for angular directives
    Route::get('directives/{directive}', function($directive) {
        return view("directives.{$directive}");
    });

    # Tutor auto login
    Route::get('login/{hash}', 'PagesController@login');

    # Tutor profile page
    Route::get('{id}', 'PagesController@tutor')->where('id', '[0-9]+');;

    # MAIN ALL PAGES ROUTE
    Route::get('{url?}', 'PagesController@index');
