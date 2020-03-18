<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'partner_project', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );

            // partner reference
            $table->string( 'partner_code' );
            $table->foreign( 'partner_code' )->references( 'code' )->on( 'partners' )->onDelete( 'cascade' );

            // project reference
            $table->string( 'project_code' );
            $table->foreign( 'project_code' )->references( 'code' )->on( 'projects' )->onDelete( 'cascade' );

            $table->string( 'default', 10 )->nullable();

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
        Schema::dropIfExists( 'partner_project' );
    }
}
