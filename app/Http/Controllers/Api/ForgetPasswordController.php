<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\ForgetPasswordService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class ForgetPasswordController extends Controller
{
    use ApiResponse;

    private ForgetPasswordService $forgetPasswordService;

    public function __construct(ForgetPasswordService $forgetPasswordService)
    {
        $this->middleware("limitReq");
        $this->forgetPasswordService = $forgetPasswordService;
    }

    public function forgotPassword(ForgetPasswordRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $this->forgetPasswordService->generateResetToken($validatedData['emailOrPhone']);
            return $this->success(['message' => 'Password reset link has been sent']);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $this->forgetPasswordService->resetPassword($validatedData['token'], $validatedData['password']);
            return $this->success(['message' => 'Password has been reset successfully']);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
