<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) { // renamed later to 'orders'
            $table->bigIncrements('id');

            // code (for MP external_reference)
            $table->string('code', 100)->nullable()->default(null)->unique();

            // User reference
            $table->unsignedBigInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users');

            // amounts
            $table->double('total_amount', 12, 2)->nullable()->default(null);
            $table->double('total_tax', 12, 2)->nullable()->default(null);

            // status
            $table->string('status', 100)->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
