<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $authService;

    function __construct(AuthService $authService)
    {
        $this->middleware("auth:sanctum")->only('logout');
        $this->middleware("limitReq");
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request->only('emailOrPhone', 'password'));
    }

    public function AdminLogin(LoginRequest $request)
    {
        return $this->authService->adminLogin($request->only('emailOrPhone', 'password'));
    }

    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request->validated(), $request->file('image'));
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request->user());
    }
}
