<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_files', function (Blueprint $table) {
            $table->increments('id');

            // Purchase reference
            $table->unsignedInteger('purchase_id')->nullable()->default(null);
            $table->foreign('purchase_id')->references('id')->on('purchases');

            // Google Storage file path
            $table->string('file_path', 100)->nullable()->default(null);
            $table->string('bucket_name', 100)->nullable()->default(null);

            // file info
            $table->string('file_name', 100)->nullable()->default(null);
            $table->integer('row_quantity')->nullable()->default(null);
            $table->text('filters')->nullable()->default(null);

            // amounts
            $table->double('amount', 12, 2)->nullable()->default(null);
            $table->double('tax', 12, 2)->nullable()->default(null);

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
        Schema::dropIfExists('purchase_files');
    }
}
