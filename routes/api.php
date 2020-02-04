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

    Route::get( 'dashboard/profile', 'API\Dashboard\ProfileAPIController@show' )->name( 'dashboard.profile.show' )
        ->middleware( 'can:manage.own.profile' );

    Route::put( 'dashboard/profile', 'API\Dashboard\ProfileAPIController@update' )->name( 'dashboard.profile.put' )
        ->middleware( 'can:manage.own.profile' );

    Route::patch( 'dashboard/profile', 'API\Dashboard\ProfileAPIController@update' )->name( 'dashboard.profile.patch' )
        ->middleware( 'can:manage.own.profile' );

    // users management

    Route::get( 'dashboard/admin/users', 'API\Dashboard\UsersManagementAPIController@index' )->name( 'dashboard.usersManagement.index' )
        ->middleware( 'can:manage.users' );

    Route::put( 'dashboard/admin/users/{userId}', 'API\Dashboard\UsersManagementAPIController@update' )->name( 'dashboard.usersManagement.update' )
        ->middleware( 'can:manage.users' );

    // orders

    Route::get( 'dashboard/orders', 'API\Dashboard\OrdersAPIController@index' )->name( 'orders.index' )
        ->middleware( 'can:see.own.orders.list' );

    Route::get( 'dashboard/orders/{orderCode}/records', 'API\Dashboard\OrdersAPIController@getJson' )->name( 'orders.getJson' )
        ->middleware( 'can:see.own.order' );

    Route::get( 'dashboard/orders/{orderCode}/download', 'API\Dashboard\OrdersAPIController@downloadFile' )->name( 'orders.downloadFile' )
        ->middleware( 'can:download.own.order' );

    // multi-api info

    Route::get( 'dashboard/multi-api/index', 'API\Dashboard\ProjectsAPIController@index' )->name( 'dashboard.multi-api.index' );

    Route::get( 'dashboard/multi-api/get_front_info', 'API\Dashboard\ProjectsAPIController@getFrontInfo' )->name( 'dashboard.multi-api.getFrontInfo' );

    // payments

    Route::post( 'dashboard/payments/process', 'API\Dashboard\OrdersPaymentAPIController@pay' )->name( 'payments.pay' )
        ->middleware( 'can:pay.own.order' );

    // peru properties

    Route::get( 'peru_properties/filters/property_type', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@getPropertyTypeFilterData' )->name( 'peru_properties.filters.propertyType' );

    Route::get( 'peru_properties/ghost_search', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@ghostSearch' )->name( 'peru_properties.ghostSearch' )
        ->middleware( 'can:search.properties' );

    Route::post( 'peru_properties/search', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@searchProperties' )->name( 'peru_properties.searchProperties' )
        ->middleware( 'can:search.properties' );

    Route::post( 'peru_properties/paginate', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@paginateProperties' )->name( 'peru_properties.paginateProperties' )
        ->middleware( 'can:search.properties' );

    Route::post( 'peru_properties/order', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@order' )->name( 'peru_properties.processOrder' )
        ->middleware( 'can:order.properties' );

} );

// generate peru properties profile
Route::get( 'peru_properties/generate_file', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@generatePropertiesFile' )->name( config( 'multi-api.pe-properties.backend-info.generate_file_url' ) );




# DOCUMENTAR
// IPN para que mercadopago notifique cuando una transaccion fue completada
// http://panel.tasing.pe/api/dashboard/notifications/mp
Route::post( 'dashboard/notifications/mp', 'API\Dashboard\MercadopagoAPIController@ipnNotification' );
