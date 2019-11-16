<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsBundleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_bundle', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bundle_id');
            $table->unsignedInteger('product_id');
            $table->timestamps();

            $table->unique(['bundle_id', 'product_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_bundle');
    }
}
