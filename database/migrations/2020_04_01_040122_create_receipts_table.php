<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'receipts', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );

            $table->string( 'code', 100 )->nullable()->default( null )->unique();
            $table->morphs( 'receiptable' );

            // status
            $table->string( 'status', 100)->nullable()->default( null );

            $table->string( 'currency', 100 )->nullable()->default( null )->comment( 'currency iso code, e.g: PEN (peruvian sol).' );

            // amounts
            $table->double( 'total_amount', 12, 2)->nullable()->default( null );
            $table->double( 'total_tax', 12, 2)->nullable()->default( null );

            $table->string( 'payment_type', 100 )->nullable()->default( null )->comment( 'payment type, e.g: mercadopago. if null, no payment was made.' );
            $table->jsonb( 'payment_info' )->nullable()->default( null );

            $table->timestamps();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'receipts' );
    }
}
