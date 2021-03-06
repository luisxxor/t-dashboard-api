<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get( 'logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index' );

// Download files
Route::name( 'downloadFiles' )
    ->get(
        '/download_files/{fileName}',
        function ( $fileName ) {
            return response()->download( config( 'app.temp_path' ) . $fileName );
        }
    )
    ->where( [ 'file' => '(.*?)\.(xls|xlsx|csv|pdf|json)$' ] );
