<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'users', function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->string( 'name' );
            $table->string( 'lastname' );
            $table->string( 'email' )->unique();

            // additional personal info
            $table->string( 'phone_number1' )->nullable()->default( null );
            $table->string( 'phone_number2' )->nullable()->default( null );
            $table->string( 'country_code' )->nullable()->default( null );
            $table->string( 'address_line1' )->nullable()->default( null );
            $table->string( 'address_line2' )->nullable()->default( null );
            $table->string( 'city' )->nullable()->default( null );
            $table->string( 'region' )->nullable()->default( null );
            $table->string( 'zipcode' )->nullable()->default( null );
            $table->string( 'company_name' )->nullable()->default( null );
            $table->string( 'company_number' )->nullable()->default( null );

            $table->timestamp( 'email_verified_at' )->nullable();
            $table->string( 'password' );
            $table->rememberToken();
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
        Schema::dropIfExists( 'users' );
    }
}
