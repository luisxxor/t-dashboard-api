<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
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
        Schema::create( 'data_tokens', function ( Blueprint $table ) {
            $table->jsonb( 'data' );
            $table->string( 'token' );
            $table->primary( 'token' );
            $table->timestamps();
            $table->decimal( 'expires_at', 15, 5 )->default( $this->epochValue( 600 ) ); // 600 seconds = 10 min
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

    /**
     * Get epoch timestamp column data value.
     *
     * @param int $seconds
     * @throws \Exception
     *
     * @return \Illuminate\Database\Query\Expression
     */
    protected function epochValue( int $seconds ): Expression
    {
        switch ( config( 'database.default' ) ) {
            case 'pgsql':
                return DB::raw( '(extract(epoch from now()) + ' . $seconds . ')' );
                break;

            case 'mysql':
                return DB::raw( '(unix_timestamp() + ' . $seconds . ')' );
                break;

            default:
                throw new \Exception( 'You need to define an epoch timestamp for database: ' . config( 'database.default' ) );
        }
    }
}
