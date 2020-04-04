<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'orders', function ( Blueprint $table ) {
            $table->string( 'project' )->nullable()->default( null );
            $table->foreign( 'project' )->references( 'code' )->on( 'projects' )->onDelete( 'no action' );
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
            $table->dropColumn( 'project' );
        } );
    }
}
