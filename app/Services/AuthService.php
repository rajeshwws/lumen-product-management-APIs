<?php

namespace App\Services;


use App\Components\CustomException;
use App\Components\ErrorMessage;
use App\Components\TokenManager;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct()
    {
    }

    /**
     * @param array $data
     * @return User
     * @throws CustomException
     */
    public function createUser(array $data) : User
    {
        $user = User::where('email', $data['email'])->first();

        if (!is_null($user)) {
            throw new CustomException('Email has been taken', ErrorMessage::RECORD_EXISTING);
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return $user;
    }

    /**
     * @param array $data
     * @return array
     * @throws CustomException
     */
    public function userLogin(array $data) : array
    {

        $user = User::where('email', $data['email'])->first();



        if (is_null($user)) {
            throw new CustomException('Wrong username or password', ErrorMessage::RECORD_NOT_EXISTING);
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw new CustomException('Wrong username or password', ErrorMessage::ACCESS_DENIED);
        }

        $apiToken = TokenManager::generateApiToken();

        $user->api_token = $apiToken;
        $user->save();

        return ['api_token' => $apiToken];
    }

}
