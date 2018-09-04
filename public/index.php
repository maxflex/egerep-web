<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/
session_start();
header("Access-Control-Allow-Origin: http://www.ege-centr.ru");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
if (! isset($_SESSION['sent_ids'])) {
    $_SESSION['sent_ids'] = [];
}

define('AB_TEST_KEY', 'ab-test-call-button-inside-tutor');
define('AB_TEST_TUTOR_OVERLAY', 'ab-test-tutor-overlay');

if (! isset($_COOKIE[AB_TEST_KEY])) {
    $variant = mt_rand(0, 1);
    setcookie(AB_TEST_KEY, $variant, time() + (86400 * 30 * 3), '/'); // кука на 3 месяца
    $_COOKIE[AB_TEST_KEY] = $variant;
}

if (isset($_GET['af'])) {
    setcookie(AB_TEST_KEY, $_GET['af'], time() + (86400 * 30 * 3), '/');
    setcookie(AB_TEST_TUTOR_OVERLAY, $_GET['af'], time() + (86400 * 30 * 3), '/');
    $_COOKIE[AB_TEST_KEY] = $_GET['af'];
    $_COOKIE[AB_TEST_TUTOR_OVERLAY] = $_GET['af'];
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
