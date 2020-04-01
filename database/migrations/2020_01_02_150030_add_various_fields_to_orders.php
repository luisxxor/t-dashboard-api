<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariousFieldsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'orders', function ( Blueprint $table ) {
            $table->integer( 'total_rows_quantity' )->nullable()->default( null );
            $table->jsonb( 'files_info' )->nullable()->default( null );
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'orders', function ( Blueprint $table ) {
            $table->dropColumn( 'total_rows_quantity' );
            $table->dropColumn( 'files_info' );
        } );
    }
}
