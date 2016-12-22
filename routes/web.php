<?php
    use App\Models\Variable;

    URL::forceSchema('https');

    # Статические страницы
    Route::get('/', function() {
        $html = Variable::display('page-index');
        $_SESSION['action'] = 'main';
        return $html;
    });

    Route::get('/request', function() {
        $html = Variable::display('page-tutor-request');
        $_SESSION['action'] = 'request';
        return $html;
    });

    Route::get('/cv', function() {
        $html = Variable::display('page-cv');
        $_SESSION['action'] = 'cv';
        return $html;
    });

    Route::get('/login', function() {
        $html = Variable::display('page-login');
        $_SESSION['action'] = 'login';
        return $html;
    });

    Route::get('/full', function() {
        unset($_SESSION['force_mobile']);
        $_SESSION['force_full'] = true;
        $_SESSION['page_was_refreshed'] = true;
        return redirect()->back();
    });

    Route::get('/mobile', function() {
        unset($_SESSION['force_full']);
        $_SESSION['force_mobile'] = true;
        $_SESSION['page_was_refreshed'] = true;
        return redirect()->back();
    });

    # Templates for angular directives
    Route::get('directives/{directive}', function($directive) {
        return view("directives.{$directive}");
    });

    # Tutor auto login
    Route::get('login/{hash}', 'PagesController@login');

    # Tutor profile page
    Route::get('{id}', 'PagesController@tutor')->where('id', '[0-9]+');

    # All serp pages
    Route::get('{url?}', 'PagesController@index');
