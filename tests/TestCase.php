<?php

use App\Models\Product;
use App\Models\ProductType;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    use \Laravel\Lumen\Testing\DatabaseMigrations;
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }


    /**
     * @param int $total
     * @return mixed
     */
    public function createTestProducts(int $total = 10)
    {
        $product_type = ProductType::find(1);

        $products = factory(Product::class, $total)->create(['product_type_id' => $product_type->id]);

        $products->map(function ($product) {
            $product->price()->create([
                'price' => 10,
                'discount' => 2
            ]);
        });

        return $products;
    }
}
