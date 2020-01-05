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

    // purchases
    Route::get( 'dashboard/purchases', 'API\Dashboard\PurchasesAPIController@index' )->name( 'purchases.index' );
    Route::get( 'dashboard/purchases/{purchaseCode}/records', 'API\Dashboard\PurchasesAPIController@show' )->name( 'purchases.show' );

    // multi-api info
    Route::get( 'dashboard/multi-api/index', 'API\Dashboard\ProjectsAPIController@index' )->name( 'dashboard.multi-api.index' );
    Route::get( 'dashboard/multi-api/get_front_info', 'API\Dashboard\ProjectsAPIController@getFrontInfo' )->name( 'dashboard.multi-api.getFrontInfo' );

    // peru properties
    Route::get( 'peru_properties/index', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@index' )->name( 'peru_properties.index' );
    Route::get( 'peru_properties/ghost_search', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@ghostSearch' )->name( 'peru_properties.ghostSearch' );
    Route::post( 'peru_properties/properties_ajax', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@searchProperties' )->name( 'peru_properties.searchProperties' );
    Route::post( 'peru_properties/properties_paginate', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@paginateProperties' )->name( 'peru_properties.paginateProperties' );
    Route::post( 'peru_properties/process_purchase', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@processPurchase' )->name( 'peru_properties.processPurchase' );
    Route::get( 'peru_properties/purchases/{purchaseCode}/download', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@downloadPurchasedFile' )->name( 'peru_properties.download' );

    // peru vehicles (coming soon)
    // Route::get( 'peru_vehicles/index', 'API\PeruVehicles\VehiclesAPIController@index' )->name( 'peru_vehicles.index' );
    // Route::post( 'peru_vehicles/properties_ajax', 'API\PeruVehicles\VehiclesAPIController@searchVehicles' )->name( 'peru_vehicles.searchVehicles' );
    // Route::post( 'peru_vehicles/properties_paginate', 'API\PeruVehicles\VehiclesAPIController@paginateVehicles' )->name( 'peru_vehicles.paginateVehicles' );
    // Route::post( 'peru_vehicles/properties_paginate_resume', 'API\PeruVehicles\VehiclesAPIController@paginatePropertiesResume' )->name( 'peru_properties.paginatePropertiesResume' );
} );

// generate peru properties profile
Route::get( 'peru_properties/generate_file', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@generatePropertiesFile' )->name( \Config::get( 'multi-api.pe-properties.backend-info.generate_file_url' ) );




# DOCUMENTAR
// IPN para que mercadopago notifique cuando una transaccion fue completada
// http://panel.tasing.pe/api/dashboard/notifications/mp
Route::post( 'dashboard/notifications/mp', 'API\Dashboard\MercadoPagoAPIController@ipnNotification' );
