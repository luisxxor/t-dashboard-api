<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'orders', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );

            // code (for MP external_reference)
            $table->string( 'code', 100)->nullable()->default( null )->unique();

            // searches reference (mongodb collection)
            $table->string( 'search_id' )->nullable()->default( null );

            // User reference
            $table->unsignedBigInteger( 'user_id' )->nullable()->default( null );
            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' );

            // status
            $table->string( 'status', 100)->nullable()->default( null );

            $table->timestamps();
            $table->softDeletes();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'orders' );
    }
}
