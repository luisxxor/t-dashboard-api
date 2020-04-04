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

Route::post( 'tokens/auth_token', 'API\Tokens\DataTokensAPIController@create' )->name( 'tokens.dataToken.create' );

Route::prefix( 'dashboard' )->middleware( 'auth:api', 'verified' )->group( function () {

    // profile

    Route::get( 'profile', 'API\Dashboard\ProfileAPIController@show' )->name( 'dashboard.profile.show' )
        ->middleware( 'can:manage.own.profile' );

    Route::put( 'profile', 'API\Dashboard\ProfileAPIController@update' )->name( 'dashboard.profile.put' )
        ->middleware( 'can:manage.own.profile' );

    Route::patch( 'profile', 'API\Dashboard\ProfileAPIController@update' )->name( 'dashboard.profile.patch' )
        ->middleware( 'can:manage.own.profile' );

    // orders

    Route::get( 'orders', 'API\Dashboard\OrdersAPIController@index' )->name( 'dashboard.orders.index' )
        ->middleware( 'can:see.own.orders.list' );

    Route::get( 'orders/{orderCode}/get_file', 'API\Dashboard\OrdersAPIController@getFile' )->name( 'dashboard.orders.getFile' )
        ->middleware( 'can:see.own.order' );

    Route::get( 'orders/{orderCode}/download_file', 'API\Dashboard\OrdersAPIController@downloadFile' )->name( 'dashboard.orders.downloadFile' )
        ->middleware( 'can:download.own.order' );

    // multi-api info

    Route::get( 'multi-api/index', 'API\Dashboard\ProjectsAPIController@index' )->name( 'dashboard.multi-api.index' );

    Route::get( 'multi-api/get_front_info', 'API\Dashboard\ProjectsAPIController@getFrontInfo' )->name( 'dashboard.multi-api.getFrontInfo' );

    // payments

    Route::post( 'payments/process', 'API\Dashboard\OrdersPaymentAPIController@pay' )->name( 'dashboard.payments.pay' )
        ->middleware( 'can:pay.own.order' );

    // projects access

    Route::get( 'projects_access', 'API\Dashboard\ProjectsAccessAPIController@index' )->name( 'dashboard.projects_access.index' );

    Route::post( 'projects_access/request', 'API\Dashboard\ProjectsAccessAPIController@request' )->name( 'dashboard.projects_access.request' );
} );

Route::prefix( 'admin' )->middleware( 'auth:api', 'verified' )->group( function () {

    // users management

    Route::get( 'roles', 'API\Admin\RolesAPIController@index' )->name( 'admin.roles.index' )
        ->middleware( 'can:manage.users' );

    Route::get( 'users', 'API\Admin\UsersAPIController@index' )->name( 'admin.users.index' )
        ->middleware( 'can:manage.users' );

    Route::put( 'users/{userId}', 'API\Admin\UsersAPIController@update' )->name( 'admin.users.update' )
        ->middleware( 'can:manage.users' );

    // projects access

    Route::get( 'projects_access', 'API\Admin\ProjectsAccessAPIController@index' )->name( 'admin.projects_access.index' );

    Route::put( 'projects_access/{projectAccessRequestId}', 'API\Admin\ProjectsAccessAPIController@update' )->name( 'admin.projects_access.update' );
} );

Route::prefix( 'peru_properties' )->middleware( 'auth:api', 'verified' )->group( function () {

    // peru properties

    Route::get( 'filters/property_type', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@getPropertyTypeFilterData' )->name( 'peru_properties.filters.propertyType' )
        ->middleware( 'scopes:access-pe-properties' );

    Route::get( 'ghost_search', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@ghostSearch' )->name( 'peru_properties.ghostSearch' )
        ->middleware( 'can:search.properties' )
        ->middleware( 'scopes:access-pe-properties' );

    Route::post( 'search', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@searchProperties' )->name( 'peru_properties.searchProperties' )
        ->middleware( 'can:search.properties' )
        ->middleware( 'scopes:access-pe-properties' );

    Route::get( 'paginate', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@paginateSearch' )->name( 'peru_properties.paginateSearch' )
        ->middleware( 'can:search.properties' )
        ->middleware( 'scopes:access-pe-properties' );

    Route::get( 'count', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@countSearch' )->name( 'peru_properties.countSearch' )
        ->middleware( 'can:search.properties' )
        ->middleware( 'scopes:access-pe-properties' );

    Route::post( 'order', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@order' )->name( 'peru_properties.processOrder' )
        ->middleware( 'can:order.properties' )
        ->middleware( 'scopes:access-pe-properties' );
} );

// generate peru properties profile
Route::get( 'peru_properties/generate_file', '\App\Projects\PeruProperties\Controllers\PropertiesAPIController@generatePropertiesFile' )->name( config( 'multi-api.pe-properties.backend-info.generate_file_url' ) );


Route::prefix( 'ecuador_properties' )->middleware( 'auth:api', 'verified' )->group( function () {

    // ecuador properties

    Route::get( 'filters/property_type', '\App\Projects\EcuadorProperties\Controllers\PropertiesAPIController@getPropertyTypeFilterData' )->name( 'ecuador_properties.filters.propertyType' );

    Route::get( 'filters/publication_type', '\App\Projects\EcuadorProperties\Controllers\PropertiesAPIController@getPublicationTypeFilterData' )->name( 'ecuador_properties.filters.publicationType' );

    Route::get( 'ghost_search', '\App\Projects\EcuadorProperties\Controllers\PropertiesAPIController@ghostSearch' )->name( 'ecuador_properties.ghostSearch' )
        ->middleware( 'can:search.properties' );

    Route::post( 'search', '\App\Projects\EcuadorProperties\Controllers\PropertiesAPIController@searchProperties' )->name( 'ecuador_properties.searchProperties' )
        ->middleware( 'can:search.properties' );

    Route::post( 'paginate', '\App\Projects\EcuadorProperties\Controllers\PropertiesAPIController@paginateProperties' )->name( 'ecuador_properties.paginateProperties' )
        ->middleware( 'can:search.properties' );

    Route::post( 'order', '\App\Projects\EcuadorProperties\Controllers\PropertiesAPIController@order' )->name( 'ecuador_properties.processOrder' )
        ->middleware( 'can:order.properties' );
} );

// generate ecuador properties profile
Route::get( 'ecuador_properties/generate_file', '\App\Projects\EcuadorProperties\Controllers\PropertiesAPIController@generatePropertiesFile' )->name( config( 'multi-api.ec-properties.backend-info.generate_file_url' ) );


Route::prefix( 'chile_properties' )->middleware( 'auth:api', 'verified' )->group( function () {

    // chile properties

    Route::get( 'filters/property_type', '\App\Projects\ChileProperties\Controllers\PropertiesAPIController@getPropertyTypeFilterData' )->name( 'chile_properties.filters.propertyType' );

    Route::get( 'filters/publication_type', '\App\Projects\ChileProperties\Controllers\PropertiesAPIController@getPublicationTypeFilterData' )->name( 'ecuador_properties.filters.publicationType' );

    Route::get( 'ghost_search', '\App\Projects\ChileProperties\Controllers\PropertiesAPIController@ghostSearch' )->name( 'chile_properties.ghostSearch' )
        ->middleware( 'can:search.properties' );

    Route::post( 'search', '\App\Projects\ChileProperties\Controllers\PropertiesAPIController@searchProperties' )->name( 'chile_properties.searchProperties' )
        ->middleware( 'can:search.properties' );

    Route::post( 'paginate', '\App\Projects\ChileProperties\Controllers\PropertiesAPIController@paginateProperties' )->name( 'chile_properties.paginateProperties' )
        ->middleware( 'can:search.properties' );

    Route::post( 'order', '\App\Projects\ChileProperties\Controllers\PropertiesAPIController@order' )->name( 'chile_properties.processOrder' )
        ->middleware( 'can:order.properties' );
} );

// generate chile properties profile
Route::get( 'chile_properties/generate_file', '\App\Projects\ChileProperties\Controllers\PropertiesAPIController@generatePropertiesFile' )->name( config( 'multi-api.cl-properties.backend-info.generate_file_url' ) );




# DOCUMENTAR
// IPN para que mercadopago notifique cuando una transaccion fue completada
// http://panel.tasing.pe/api/dashboard/notifications/mp
Route::post( 'dashboard/notifications/mp', 'API\IPN\MercadopagoAPIController@ipnNotification' );
