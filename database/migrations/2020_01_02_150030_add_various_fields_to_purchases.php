<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariousFieldsToPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'purchases', function ( Blueprint $table ) {
            $table->integer( 'total_rows_quantity' )->nullable()->default( null );
            $table->string( 'currency', 100 )->nullable()->default( null )->comment( 'currency iso code, e.g: PEN (peruvian sol).' );
            $table->string( 'payment_type', 100 )->nullable()->default( null )->comment( 'payment type, e.g: mercadopago. if null, no payment was made.' );
            $table->jsonb( 'payment_info' )->nullable()->default( null );
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
        Schema::table( 'purchases', function ( Blueprint $table ) {
            $table->dropColumn( 'total_rows_quantity' );
            $table->dropColumn( 'currency' );
            $table->dropColumn( 'payment_type' );
            $table->dropColumn( 'payment_info' );
            $table->dropColumn( 'files_info' );
        } );
    }
}
