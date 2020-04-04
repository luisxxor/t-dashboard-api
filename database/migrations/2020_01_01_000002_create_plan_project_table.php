<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'plan_project', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );

            // projects reference
            $table->string( 'project_code' );
            $table->foreign( 'project_code' )->references( 'code' )->on( 'projects' )
                ->onDelete( 'cascade' )->onUpdate( 'cascade' );

            // plans reference
            $table->unsignedBigInteger( 'plan_id' );
            $table->foreign( 'plan_id' )->references( 'id' )->on( 'plans' )
                ->onDelete( 'cascade' )->onUpdate( 'cascade' );

            // there can only be one plan project combination
            $table->unique( [ 'project_code', 'plan_id' ] );

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
        Schema::dropIfExists( 'plan_project' );
    }
}
