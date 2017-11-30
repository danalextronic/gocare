<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => 'web'], function () {

    Route::get('/orderstest', 'MagentoController@testOrder');

    Route::get('/logout', 'Auth\AuthController@getLogout');
    Route::get('/login', 'Auth\AuthController@getLogin');
    Route::post('/login', 'Auth\AuthController@postLogin');
    Route::get('/home', 'HomeController@index');

    Route::group(['middleware' => 'auth'], function () {
        // user management
        Route::get('/users', 'UsersController@listUsers');
        Route::get('/users/create', 'UsersController@create');
        Route::get('/users/{user}', 'UsersController@user')->where('user', '\\d+');
        Route::post('/users/{user}', 'UsersController@updateUser')->where('user', '\\d+');
        Route::post('/users/create', 'UsersController@postCreate');
        // end user management

        Route::get('/orders', 'OrdersController@index');

        Route::get('/orders/imports', 'OrdersController@getImports');
        Route::get('/orders/imports/create', 'OrdersController@getImport');
        Route::post('/orders/imports', 'OrdersController@postImport');

        Route::get('/orders/failed/{code?}', 'OrdersController@getFailedOrders');
        Route::get('/orders/download/{status?}', 'OrdersController@downloadCsv');
        Route::get('/orders/create', 'OrdersController@create');
        Route::post('/orders', 'OrdersController@store');

        Route::get('/orders/{orderId}', 'OrdersController@edit');
        Route::post('/orders/{orderId}', 'OrdersController@update');

        Route::resource('/apikeys', 'ApikeysController');
//        Route::resource('/orders', 'OrdersController');
        Route::resource('/claims', 'ClaimsController');

    });

});

Route::group(['middleware' => 'api'], function () {
    Route::post('/api/v1/orders', 'API\OrdersController@store');
    Route::get('/api/v1/devices/search/', 'API\DevicesController@search');
    Route::resource('/api/v1/imports', 'API\ImportsController');
});

/////////// test routes ///////////////
Route::get('/testcleansku', 'MagentoController@testCleanSku');
Route::get('/testorder', 'MagentoController@testOrder');
Route::get('/testMagentoCustomerCreate', 'MagentoController@testMagentoCustomerCreate');
