<?php

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductType;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ProductTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function a_product_has_a_type()
    {
        $product_type = ProductType::find(1);

        $product = factory(Product::class)->create(['product_type_id' => $product_type->id]);

        $this->assertEquals($product->productType->id, $product_type->id);
    }

    /**
     * @test
     */
    public function a_product_type_has_many_product()
    {
        $product_type = ProductType::find(1);

        $products = factory(Product::class, 5)->create(['product_type_id' => $product_type->id]);

        $this->assertEquals($product_type->products->count(), $products->count());
    }

    /**
     * @test
     */
    public function a_product_has_a_price()
    {
        $product = factory(Product::class)->create();

        $this->assertFalse($product->hasPrice());

        $product->price()->create([
            'price' => 1000,
            'discount' => 200
        ]);

        $product->refresh();

        $this->assertTrue($product->hasPrice());

        $price = $product->price;

        $this->assertEquals($price->product->id, $product->id);
    }

    /**
     * @test
     */
    public function a_bundle_product_has_sub_products()
    {
        $parentProduct = factory(Product::class)->create(['product_type_id' => ProductType::BUNDLE_PRODUCT_ID]);

        $sub_products = factory(Product::class, 5)->create(['product_type_id' => ProductType::SIMPLE_PRODUCT_ID]);

        $sub_products->map(function ($product) use ($parentProduct) {
            $parentProduct->bundle()->create(['product_id' => $product->id]);
        });

        $this->assertEquals($parentProduct->bundle->count(), 5);

        $sub_product = $parentProduct->bundle->first();

        $this->assertEquals($sub_product->parentProduct->id, $parentProduct->id);

    }

    /**
     * @test
     */
    public function a_sub_product_of_a_bundle_is_also_a_product()
    {

        $parentProduct = factory(Product::class)->create(['product_type_id' => ProductType::BUNDLE_PRODUCT_ID]);
        $sampleSubProduct = factory(Product::class)->create(['product_type_id' => ProductType::SIMPLE_PRODUCT_ID]);

        $parentProduct->bundle()->create(['product_id' => $sampleSubProduct->id]);

        $sub_product = ProductBundle::where(['bundle_id' => $parentProduct->id, 'product_id' => $sampleSubProduct->id])->first();

        $this->assertEquals($sub_product->subProduct->id, $sampleSubProduct->id);

        $this->assertEquals($sub_product->parentProduct->id, $parentProduct->id);
    }

    /**
     * @test
     */
    public function a_simple_product_is_not_a_bundle()
    {
        $product_type = ProductType::find(1);

        $product = factory(Product::class)->create(['product_type_id' => $product_type->id]);

        $this->assertFalse($product->isBundle());
    }
}
