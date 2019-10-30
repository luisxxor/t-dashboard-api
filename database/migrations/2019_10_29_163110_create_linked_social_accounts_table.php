<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkedSocialAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'linked_social_accounts', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );

            $table->string( 'provider_id' );
            $table->string( 'provider_name' );

            // User reference
            $table->unsignedBigInteger( 'user_id' )->nullable()->default( null );
            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' );

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
        Schema::dropIfExists( 'linked_social_accounts' );
    }
}
