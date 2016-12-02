<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/


Route::group(
    [
        'prefix' => 'auth',
    ],
    function () {
        Route::get('/slack', 'Auth\LoginController@authSlack')->name('auth.slack');
        Route::get('/slack/callback', 'Auth\LoginController@authSlackCallback')->name('auth.slack.callback');
    }
);

Route::get('/', 'DefaultController@getIndex')->name('index');
Route::get('/{user}', 'DefaultController@getProfile')->name('profile');