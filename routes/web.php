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
    $app->group(['prefix' => 'auth'], function () use ($app){
        $app->post('signin', 'AuthenticationController@signin');
        $app->post('signin/google', 'AuthenticationController@signinGoogle');
    });
});

$app->get('/test', function () use ($app){
   return hash_hmac('sha1',env('DOKU_CLIENT_ID').env('DOKU_SHARED_KYE').'1',env('DOKU_CLIENT_SECRET'));
});

$app->get('/signon', function () use ($app){

    $clientId = env('DOKU_CLIENT_ID');
    $clientSecret = env('DOKU_CLIENT_SECRET');
    $sharedkey = env('DOKU_SHARED_KYE');

//    return $clientId.'|'.$clientSecret.'|'.$sharedkey;

    $key="APIKEY";
    $timestamp=time();
    $new_systrace=2;

    //form letters
    $letters=$clientId.$sharedkey.$new_systrace;

    //get words
    $words=hash_hmac('sha1',$letters,$clientSecret);

    $signature = $words;
    $postfields="clientId={$clientId}&clientSecret={$clientSecret}&sharedKey={$sharedkey}&systrace={$new_systrace}&words={$words}&version=1.0&responseType=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://dev.dokupay.com/dokupay/h2h/signon");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields) ;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded","key: ".$key,"sig: ".$signature));
    $response = curl_exec($ch);
    curl_close($ch);

    $message_decode=json_decode($response);

//    return response($message_decode);
    var_dump($message_decode);exit;

   //return hash_hmac('sha1',env('DOKU_CLIENT_ID').env('DOKU_SHARED_KYE').'1',env('DOKU_CLIENT_SECRET'));
});