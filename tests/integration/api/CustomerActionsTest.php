<?php

use App\Components\TokenManager;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Faker\Factory;

class CustomerActionsTest extends TestCase
{
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     * @return User
     */
    public function generateUserWithToken(): User
    {
        //given that we have a admin with access token
        $user = factory(User::class)->create();

        // create access token for user
        $apiToken = TokenManager::generateApiToken();

        $user->api_token = $apiToken;
        $user->save();

        return $user;
    }

    /**
     * @test
     */
    public function a_user_can_add_item_to_cart()
    {
        $products = $this->createTestProducts(3);

        $valid_product_ids = $products->pluck('id')->all();

        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];

        foreach ($valid_product_ids as $product_id) {
            $post_data = [
                'product_id' => $product_id,
                'qty' => 2,
            ];
            $this->post('/api/cart', $post_data, $header)
                ->seeJsonContains([
                    'status' => 'success',
                    'message' => 'item added to cart',
                    'product_id' => $valid_product_ids[0]
                ]);
        }

        // test to update cart Items
        $post_data = [
            'product_id' => $valid_product_ids[0],
            'qty' => 1,
        ];

        $this->post('/api/cart', $post_data, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'item added to cart',
                'product_id' => $valid_product_ids[0]
            ]);
    }

    /**
     * @test
     */
    public function a_user_can_retrieve_cart_items()
    {
        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->get('/api/cart', $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'cart items',
                'price' => "10.00"
            ]);
    }

    /**
     * @test
     */
    public function a_user_can_checkout_their_cart()
    {
        $body = [
            'address' => $this->faker->address,
            'payment_method' => $this->faker->creditCardType
        ];

        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->post('/api/cart/checkout', $body, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'you order has been received',
                'id' => 1,
                'total_price' => '70.00'
            ]);
    }

    /**
     * @test
     */
    public function a_user_can_see_a_list_of_their_orders()
    {
        $user = $this->generateUserWithToken();
        $this->generateTestOrders($user);

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->get('/api/orders', $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'all user orders fetched',
            ]);
    }

    /**
     * @test
     */
    public function a_user_can_see_order_details()
    {
        $user = $this->generateUserWithToken();
        $order = $this->generateTestOrders($user);

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->get('/api/orders/' . $order->id, $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'successfully fetched order details',
                'total_price' => "10.00"
            ]);
    }

    /**
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function generateTestOrders(User $user)
    {
        $order = $user->createOrder([
            'address' => $this->faker->address,
            'payment_method' => $this->faker->creditCardType,
            'total_price' => 10
        ]);

        $products = factory(Product::class, 3)->create();

        $products->map(function ($product) use ($order) {
            $order->orderItems()
                ->create([
                    'product_id' => $product->id,
                    'qty' => 5,
                    'price' => 5
                ]);
        });

        return $order;
    }

    /**
     * @test
     */
    public function it_fails_when_a_user_can_add_invalid_item_to_cart()
    {
        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];
        $post_data = [
            'product_id' => 400,
            'qty' => 2,
        ];

        $this->post('/api/cart', $post_data, $header)
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
    public function a_user_cannot_add_qty_greater_than_item_to_cart()
    {
        $products = $this->createTestProducts(3);

        $valid_product_ids = $products->pluck('id')->all();

        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];

        $post_data = [
            'product_id' => $valid_product_ids[0],
            'qty' => 20,
        ];
        $this->post('/api/cart', $post_data, $header)
            ->seeJsonContains([
                'code' => 'OUT_OF_STOCK',
                'status' => 'error',
                'message' => 'Product out of stock',
                'data' => []
            ]);
    }



    /**
     * @test
     */
    public function a_user_can_retrieve_empty_cart_items()
    {
        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->get('/api/cart', $header)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'cart items',
                'data' => []
            ]);
    }

    /**
     * @test
     */
    public function a_user_do_not_have_orders_if_they_were_never_created()
    {
        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->get('/api/orders', $header)
            ->seeJsonContains([
                'code' => 'RECORD_NOT_EXISTING',
                'status' => 'error',
                'message' => 'You dont have any orders yet!'
            ]);
    }

    /**
     * @test
     */
    public function a_user_cannot_see_order_details_if_order_id_is_wrong()
    {
        $user = $this->generateUserWithToken();

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->get('/api/orders/200', $header)
            ->seeJsonContains([
                'code' => 'RECORD_NOT_EXISTING',
                'status' => 'error',
                'message' => 'invalid order id!'
            ]);
    }

    /**
     * @test
     */
    public function a_user_cannot_see_order_details_if_it_belongs_to_another_user()
    {
        $user = $this->generateUserWithToken();

        $another_user = factory(User::class)->create();

        $order = $this->generateTestOrders($another_user);

        $header = [
            'Authorization' => $user->api_token
        ];

        $this->get('/api/orders/' . $order->id, $header)
            ->seeJsonContains([
                'code' => 'RECORD_NOT_EXISTING',
                'status' => 'error',
                'message' => 'Order not for user!'
            ]);
    }

}
