<?php

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

Route::prefix( 'pe-properties' )->middleware( 'auth:api', 'verified', 'scopes:access-pe-properties' )->group( function () {

    Route::get( 'filters', 'PropertiesController@filters' )->name( 'pe-properties.filters' )
        ->middleware( 'can:search.properties' );

    Route::post( 'search', 'PropertiesController@searchProperties' )->name( 'pe-properties.searchProperties' )
        ->middleware( 'can:search.properties' );

    Route::get( 'paginate', 'PropertiesController@paginateSearch' )->name( 'pe-properties.paginateSearch' )
        ->middleware( 'can:search.properties' );

    Route::get( 'count', 'PropertiesController@countSearch' )->name( 'pe-properties.countSearch' )
        ->middleware( 'can:search.properties' );

    Route::post( 'order', 'PropertiesController@order' )->name( 'pe-properties.processOrder' )
        ->middleware( 'can:order.properties' );
} );

Route::get( 'pe-properties/generate_file', 'PropertiesController@generatePropertiesFile' )->name( config( 'multi-api.pe-properties.backend-info.generate_file_url' ) );
