<?php

use App\Components\TokenManager;
use App\Models\User;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthTest extends TestCase
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
    public function a_user_can_register()
    {
        $user_data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password'
        ];

        $this->post('/api/register', $user_data)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'successfully created a new user',
                'email' => $user_data['email']
            ]);

    }

    /**
     * @test
     */
    public function registered_user_can_login()
    {
        $user = factory(User::class)->create();

        $login_details = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $this->post('/api/login', $login_details)
            ->seeJsonContains([
                'status' => 'success',
                'message' => 'access token issued'
            ]);
    }

    /**
     * @test
     */
    public function access_token_is_required_for_secured_routes()
    {
        $data = [];

        $header = [];

        $this->post('/api/products', $data, $header)
            ->seeJsonContains([
                'code' => 'ACCESS_DENIED',
                'status' => 'error',
                'message' => 'unauthorized access'
            ]);
    }

    /**
     * @test
     */
    public function a_non_admin_user_do_not_have_access_to_admin_endpoints()
    {
        $user = factory(User::class)->create();

        // create access token for user
        $apiToken = TokenManager::generateApiToken();

        $user->api_token = $apiToken;
        $user->save();

        $data = [];

        $header = [
            'Authorization' => $apiToken
        ];

        $this->post('/api/products', $data, $header)
            ->seeJsonContains([
                'code' => 'CANNOT_PERFORM_ACTION',
                'status' => 'error',
                'message' => 'Admin Permission needed'
            ]);
    }

}
