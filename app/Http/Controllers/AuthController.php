<?php

namespace App\Http\Controllers;

use App\Components\Response;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $authService;

    public function __construct(AuthService $service)
    {
        $this->authService = $service;
    }

    public function createUser(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required',
            'email'    => 'required',
            'password' => 'required'
        ]);

        $user = $this->authService->createUser($request->only('email', 'password', 'name'));

        return Response::success($user, 'successfully created a new user');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Components\CustomException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {

        $this->validate($request, [
            'email'    => 'required',
            'password' => 'required'
        ]);

        $result = $this->authService->userLogin($request->only('email', 'password', 'remember_me'));

        return Response::success($result, 'access token issued');

    }

}
