<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSearchIdToPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'purchases', function ( Blueprint $table ) {
            $table->string( 'search_id' )->nullable()->default( null );
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'purchases', function ( Blueprint $table ) {
            $table->dropColumn( 'search_id' );
        } );
    }
}
