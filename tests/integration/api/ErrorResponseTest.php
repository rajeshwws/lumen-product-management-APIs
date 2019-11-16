<?php


use App\Models\User;

class ErrorResponseTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_return_a_route_not_found_error_when_you_hit_an_invalid_route()
    {
        $this->get('/api/invalid/route')
            ->seeJsonContains([
                'code' => 'ROUTE_NOT_FOUND',
                'status' => 'error',
                'message' => '',
                'data' => null
            ]);
    }

    /**
     * @test
     */
    public function it_should_return_internal_error_when_a_general_exception_happens()
    {
        $this->get('/error')
            ->seeJsonContains([
                'code' => 'INTERNAL_ERROR',
                'status' => 'error',
                'message' => '',
                'data' => null
            ]);
    }

    /**
     * @test
     */
    public function it_should_return_invalid_input_when_a_validation_exception_happens()
    {
        $this->post('/api/register', ['name' => 'test name', 'password' => 'password'], [])
            ->seeJsonContains([
                'code' => 'INVALID_INPUT',
                'status' => 'error',
                'message' => 'The email field is required.',
                'data' => null
            ]);
    }


    /**
     * @test
     */
    public function it_should_return_record_existing_when_a_custom_exception_happens()
    {
        $user = factory(User::class)->create(['email' => 'test@email.com']);

        $data = [
            'name' => 'test name',
            'email' => $user->email,
            'password' => 'password',
        ];

        $this->post('/api/register', $data, [])
            ->seeJsonContains([
                'code' => 'RECORD_EXISTING',
                'status' => 'error',
                'message' => 'Email has been taken',
                'data' => null
            ]);
    }


    /**
     * @test
     */
    public function it_should_fail_login_when_invalid_email__is_used()
    {
        $data = [
            'name' => 'test name',
            'email' => 'sample@failed.com',
            'password' => 'password',
        ];

        $this->post('/api/login', $data, [])
            ->seeJsonContains([
                'code' => 'RECORD_NOT_EXISTING',
                'status' => 'error',
                'message' => 'Wrong username or password',
                'data' => null
            ]);
    }


    /**
     * @test
     */
    public function it_should_fail_login_when_invalid_password_is_used()
    {
        $user = factory(User::class)->create(['email' => 'test@email.com']);

        $data = [
            'name' => 'test name',
            'email' => $user->email,
            'password' => 'pass',
        ];

        $this->post('/api/login', $data, [])
            ->seeJsonContains([
                'code' => 'ACCESS_DENIED',
                'status' => 'error',
                'message' => 'Wrong username or password',
                'data' => null
            ]);
    }
}
