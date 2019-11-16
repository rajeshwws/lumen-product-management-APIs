<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->unique();
            $table->string('slug');
            $table->timestamps();
        });

        $product_types = [
            [
                'id' => 1,
                'type' => 'Single',
                'slug' => 'single'
            ],
            [
                'id' => 2,
                'type' => 'Bundle',
                'slug' => 'bundle'
            ]
        ];

        foreach ($product_types as $product_type) {
            DB::table('product_type')->insert($product_type);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_type');
    }
}
