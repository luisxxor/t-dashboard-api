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

Route::get( 'vue-app/{any}', function() {
    return view('index');
})->where( 'any','.*');

Route::get( 'logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index' );

Route::get('login/{provider}', 'SocialController@redirect');
Route::get('login/{provider}/callback','SocialController@Callback');

// Download files
Route::name( 'downloadFiles' )
    ->get(
        '/download_files/{fileName}',
        function ( $fileName ) {
            return response()->download( sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName );
        }
    )
    ->where( [ 'file' => '(.*?)\.(xls|xlsx|csv|pdf)$' ] );
