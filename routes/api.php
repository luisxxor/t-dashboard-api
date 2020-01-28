<?php

use App\Routers\AuthAPI;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

AuthAPI::routes( [ 'verify' => true ] );

Route::get( 'login/{provider}', 'API\OAuth\SocialiteAPIController@redirect' );
Route::get( 'login/{provider}/callback','API\OAuth\SocialiteAPIController@callback' );

Route::middleware( 'auth:api', 'verified' )->group( function () {
    // profile
    Route::get( 'dashboard/profile', 'API\Dashboard\ProfileAPIController@show' )->name( 'dashboard.profile.show' );
    Route::put( 'dashboard/profile', 'API\Dashboard\ProfileAPIController@update' )->name( 'dashboard.profile.put' );
    Route::patch( 'dashboard/profile', 'API\Dashboard\ProfileAPIController@update' )->name( 'dashboard.profile.patch' );

    // orders
    Route::get( 'dashboard/orders', 'API\Dashboard\OrdersAPIController@index' )->name( 'orders.index' );
    Route::get( 'dashboard/orders/{orderCode}/records', 'API\Dashboard\OrdersAPIController@show' )->name( 'orders.show' );

    // multi-api info
    Route::get( 'dashboard/multi-api/index', 'API\Dashboard\ProjectsAPIController@index' )->name( 'dashboard.multi-api.index' );
    Route::get( 'dashboard/multi-api/get_front_info', 'API\Dashboard\ProjectsAPIController@getFrontInfo' )->name( 'dashboard.multi-api.getFrontInfo' );

    // payments
    Route::post( 'dashboard/payments/process', 'API\Dashboard\PayOrderAPIController@pay' )->name( 'payments.pay' );

    // peru properties
    Route::get( 'peru_properties/index', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@index' )->name( 'peru_properties.index' );
    Route::get( 'peru_properties/ghost_search', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@ghostSearch' )->name( 'peru_properties.ghostSearch' );
    Route::post( 'peru_properties/properties_ajax', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@searchProperties' )->name( 'peru_properties.searchProperties' );
    Route::post( 'peru_properties/properties_paginate', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@paginateProperties' )->name( 'peru_properties.paginateProperties' );
    Route::post( 'peru_properties/order', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@order' )->name( 'peru_properties.processOrder' );
    Route::get( 'peru_properties/orders/{orderCode}/download', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@downloadOrderedFile' )->name( 'peru_properties.download' );

    //trancing
    Route::get( 'tracing_properties/index', '\App\Projects\TracingProperties\Controllers\TracingsAPIController@index' )->name( 'tracing_properties.index' );
    Route::post( 'tracing_properties/create_property', '\App\Projects\PeruProperties\Controllers\TracingsAPIController@createProperties' )->name( 'tracing_properties.create' );

} );

// generate peru properties profile
Route::get( 'peru_properties/generate_file', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@generatePropertiesFile' )->name( config( 'multi-api.pe-properties.backend-info.generate_file_url' ) );




# DOCUMENTAR
// IPN para que mercadopago notifique cuando una transaccion fue completada
// http://panel.tasing.pe/api/dashboard/notifications/mp
Route::post( 'dashboard/notifications/mp', 'API\Dashboard\MercadopagoAPIController@ipnNotification' );
