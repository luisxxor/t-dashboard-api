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

Route::prefix( 'peru_properties' )->middleware( 'auth:api', 'verified', 'scopes:access-pe-properties' )->group( function () {

    Route::get( 'filters/property_type', 'PropertiesController@getPropertyTypeFilterData' )->name( 'peru_properties.filters.propertyType' )
        ->middleware( 'can:search.properties' );

    Route::post( 'search', 'PropertiesController@searchProperties' )->name( 'peru_properties.searchProperties' )
        ->middleware( 'can:search.properties' );

    Route::get( 'paginate', 'PropertiesController@paginateSearch' )->name( 'peru_properties.paginateSearch' )
        ->middleware( 'can:search.properties' );

    Route::get( 'count', 'PropertiesController@countSearch' )->name( 'peru_properties.countSearch' )
        ->middleware( 'can:search.properties' );

    Route::post( 'order', 'PropertiesController@order' )->name( 'peru_properties.processOrder' )
        ->middleware( 'can:order.properties' );
} );

Route::get( 'peru_properties/generate_file', 'PropertiesController@generatePropertiesFile' )->name( config( 'multi-api.pe-properties.backend-info.generate_file_url' ) );
