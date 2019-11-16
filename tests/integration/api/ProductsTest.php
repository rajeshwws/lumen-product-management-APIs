<?php


use App\Components\TokenManager;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ProductsTest extends TestCase
{
    use DatabaseTransactions;

    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    /**
     * @test
     */
    public function anyone_can_get_a_list_of_all_products()
    {
        $this->createTestProducts();

        $this->get('/api/products')->seeJson([
            'status' => 'success',
            'message' => 'Successfully fetched all products'
        ]);
    }

    /**
     * @test
     */
    public function anyone_can_see_a_single_product()
    {
        $products = $this->createTestProducts();
        $first_product = $products[0];

        $this->get('/api/products/' . $first_product->id)->seeJsonContains([
            'status' => 'success',
            'message' => 'Successfully fetched a single product',
            'name' => $first_product->name
        ]);
    }

    /**
     * @test
     */
    public function a_single_product_can_be_created_by_admin()
    {
        $apiToken = $this->generateValidToken();

        // given that we have a product create data
        $product_data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'sku' => $this->faker->ean8,
            'qty' => 5,
            'product_type_id' => 1,
            'price' => 5000, // remember this is in cents
        ];

        $header = [
            'Authorization' => $apiToken
        ];

        $this->post('/api/products', $product_data, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'product successfully created',
                'name' => $product_data['name']
            ]);
    }

    /**
     * @test
     */
    public function a_product_bundle_can_be_created()
    {
        $products = $this->createTestProducts(3);

        $valid_product_ids = $products->pluck('id')->all();

        // given that we have a product create data
        $product_data = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'sku' => $this->faker->ean8,
            'qty' => 5,
            'product_type_id' => 2, // product type for bundle
            'price' => 5000, // remember this is in cents
            'products' => $valid_product_ids
        ];

        $header = [
            'Authorization' => $this->generateValidToken()
        ];

        $this->post('/api/products', $product_data, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'product successfully created',
                'name' => $product_data['name']
            ]);
    }

    /**
     * @return string
     */
    public function generateValidToken(): string
    {
        //given that we have a admin with access token
        $user = factory(User::class)->create(['is_admin' => true]);

        // create access token for user
        $apiToken = TokenManager::generateApiToken();

        $user->api_token = $apiToken;
        $user->save();

        return $apiToken;
    }

    /**
     * @test
     */
    public function prices_of_products_can_be_modified()
    {
        $products = $this->createTestProducts(2);

        $product = $products->first();

        $update_data = [
            'name' => 'Updated Name',
            'price' => 25.75
        ];

        $header = [
            'Authorization' => $this->generateValidToken()
        ];

        $price = $update_data['price'];
        $this->put('/api/products/' . $product->id, $update_data, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'product successfully updated',
                'name' => $update_data['name'],
                'price' => "$price"
            ]);
    }

    /**
     * @test
     */
    public function final_product_price_is_unchanged_if_discount_is_not_active()
    {
        $products = $this->createTestProducts(2);

        $product = $products->first();

        $this->get('/api/products/' . $product->id)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'Successfully fetched a single product',
                'final_price' => $product->price->price
            ]);
    }

    /**
     * @test
     */
    public function final_prices_can_be_affected_by_discount_amount()
    {
        $products = $this->createTestProducts(2);

        $product = $products->first();

        $update_data = [
            'discount' => 2,
            'discount_active' => true
        ];

        $header = [
            'Authorization' => $this->generateValidToken()
        ];

        $discount = number_format($update_data['discount'], 2);
        $this->put('/api/products/' . $product->id, $update_data, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'product successfully updated',
                'discount' => "$discount",
                'final_price' => "8.00"
            ]);
    }

    /**
     * @test
     */
    public function final_prices_can_be_affected_by_discount_percentage()
    {
        $products = $this->createTestProducts(2);

        $product = $products->first();

        $update_data = [
            'discount_percentage' => 10,
            'discount_active' => true
        ];

        $header = [
            'Authorization' => $this->generateValidToken()
        ];

        $this->put('/api/products/' . $product->id, $update_data, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'product successfully updated',
                'discount' => "1.00",
                'final_price' => "9.00"
            ]);
    }

    /**
     * @test
     */
    public function discount_can_be_disabled_on_discount_prices()
    {
        $products = $this->createTestProducts(2);

        $product = $products->first();

        $product->price->discount_active = true;

        $product->price->save();

        $update_data = [
            'discount_active' => false
        ];

        $header = [
            'Authorization' => $this->generateValidToken()
        ];

        $this->put('/api/products/' . $product->id, $update_data, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'product successfully updated',
                'final_price' => $product->price->price
            ]);
    }

    /**
     * @test
     */
    public function it_fails_to_fetch_invalid_product()
    {
        $this->get('/api/products/400')
            ->seeJsonContains([
                'code' => 'RECORD_NOT_EXISTING',
                'status' => 'error',
                'message' => 'Invalid Product id',
                'data' => null
            ]);
    }

    /**
     * @test
     */
    public function invalid_products_cannot_be_modified()
    {
        $update_data = [
            'name' => 'Updated Name'
        ];

        $header = [
            'Authorization' => $this->generateValidToken()
        ];

        $this->put('/api/products/400', $update_data, $header)
            ->seeJsonContains([
                'code' => 'RECORD_NOT_EXISTING',
                'status' => 'error',
                'message' => 'Invalid Product id',
                'data' => null
            ]);
    }

}
