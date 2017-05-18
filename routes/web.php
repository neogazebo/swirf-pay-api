<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return "Welcome to SWIRF PAY API";
});

//todo JWT Middleware
$app->group(['prefix' => 'v1', 'namespace' => 'V1','middleware' => ['SecretKey']], function() use ($app) {
    $app->post('signin', 'AuthenticationController@signup');
});