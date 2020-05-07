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

    Route::get( 'filters/property_type', 'PropertiesAPIController@getPropertyTypeFilterData' )->name( 'peru_properties.filters.propertyType' )
        ->middleware( 'can:search.properties' );

    Route::get( 'ghost_search', 'PropertiesAPIController@ghostSearch' )->name( 'peru_properties.ghostSearch' )
        ->middleware( 'can:search.properties' );

    Route::post( 'search', 'PropertiesAPIController@searchProperties' )->name( 'peru_properties.searchProperties' )
        ->middleware( 'can:search.properties' );

    Route::get( 'paginate', 'PropertiesAPIController@paginateSearch' )->name( 'peru_properties.paginateSearch' )
        ->middleware( 'can:search.properties' );

    Route::get( 'count', 'PropertiesAPIController@countSearch' )->name( 'peru_properties.countSearch' )
        ->middleware( 'can:search.properties' );

    Route::post( 'order', 'PropertiesAPIController@order' )->name( 'peru_properties.processOrder' )
        ->middleware( 'can:order.properties' );
} );

// generate peru properties profile
Route::get( 'peru_properties/generate_file', 'PropertiesAPIController@generatePropertiesFile' )->name( config( 'multi-api.pe-properties.backend-info.generate_file_url' ) );
