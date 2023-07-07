<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Route;

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});



Route::group([
    'prefix' => 'api'
], function(){
    Route::get('/', function(){
        $lumen = app();
        $version = $lumen->version();
        return $version;
    });
    Route::group([
        'middleware' => 'cors'
    ], function(){
        Route::post('/login','UsersController@login');
        
        Route::group([
            'prefix' => 'item'
        ], function(){
            Route::group([
                'middleware' => 'auth'
            ], function(){
                Route::post('/list','ItemsController@list');
                Route::post('/save','ItemsController@save');
                Route::get('/show/{id}','ItemsController@show');
                Route::post('/update','ItemsController@update');
                Route::post('/delete','ItemsController@delete');
            });
        });
        
    });
    
});