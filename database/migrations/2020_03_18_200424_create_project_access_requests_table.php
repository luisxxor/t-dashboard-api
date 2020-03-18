<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectAccessRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'project_access_requests', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );

            // user reference
            $table->unsignedBigInteger( 'user_id' );
            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' );

            // partner reference
            $table->unsignedBigInteger( 'partner_project_id' );
            $table->foreign( 'partner_project_id' )->references( 'id' )->on( 'partner_project' )->onDelete( 'cascade' );

            // status
            $table->string( 'status', 100 )->nullable()->default( null );

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
        Schema::dropIfExists( 'project_access_requests' );
    }
}
