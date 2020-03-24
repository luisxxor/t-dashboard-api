<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDataTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( config( 'database.default' ) === 'pgsql' ) {
            $epoch = DB::raw( '(extract(epoch from now()) + 600)' );
        } elseif ( config( 'database.default' ) === 'mysql' ) {
            $epoch = DB::raw( '(unix_timestamp() + 600)' );
        } else {
            throw new \Exception( 'You need to define an epoch timestamp for database: ' . config( 'database.default' ) );
        }

        Schema::create( 'data_tokens', function ( Blueprint $table ) use ( $epoch ) {
            $table->jsonb( 'data' );
            $table->string( 'token' );
            $table->primary( 'token' );
            $table->timestamps();
            $table->decimal( 'expires_at', 15, 5 )->default( $epoch );
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'data_tokens' );
    }
}
