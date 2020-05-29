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

Route::prefix( 'do-properties' )->middleware( 'auth:api', 'verified', 'scopes:access-do-properties' )->group( function () {

    Route::get( 'filters', 'PropertiesController@filters' )->name( 'do-properties.filters' )
        ->middleware( 'can:search.properties' );

    Route::post( 'search', 'PropertiesController@searchProperties' )->name( 'do-properties.search' )
        ->middleware( 'can:search.properties' );

    Route::get( 'paginate', 'PropertiesController@paginateSearch' )->name( 'do-properties.paginate' )
        ->middleware( 'can:search.properties' );

    Route::get( 'count', 'PropertiesController@countSearch' )->name( 'do-properties.count' )
        ->middleware( 'can:search.properties' );

    Route::post( 'order', 'PropertiesController@order' )->name( 'do-properties.order' )
        ->middleware( 'can:order.properties' );
} );

Route::get( 'do-properties/generate_file', 'PropertiesController@generatePropertiesFile' )->name( config( 'multi-api.do-properties.backend-info.generate_file_url' ) );
