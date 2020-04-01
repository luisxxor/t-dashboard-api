<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerProjectPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'partner_project_plan', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );

            // partner_project reference
            $table->unsignedBigInteger( 'partner_project_id' );
            $table->foreign( 'partner_project_id' )->references( 'id' )->on( 'partner_project' )
                ->onDelete( 'cascade' )->onUpdate( 'cascade' );

            // plans reference
            $table->unsignedBigInteger( 'plan_id' );
            $table->foreign( 'plan_id' )->references( 'id' )->on( 'plans' )
                ->onDelete( 'cascade' )->onUpdate( 'cascade' );

            // there can only be one plan partner_project combination
            $table->unique( [ 'partner_project_id', 'plan_id' ] );

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
        Schema::dropIfExists( 'partner_project_plan' );
    }
}
