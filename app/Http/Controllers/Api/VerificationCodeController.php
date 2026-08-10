<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VerificationCodeService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerificationCodeController extends Controller
{
    use ApiResponse;

    private VerificationCodeService $verificationCodeService;

    function __construct(VerificationCodeService $verificationCodeService)
    {
        $this->middleware("limitReq");
        $this->verificationCodeService = $verificationCodeService;
    }

    public function sendVerificationCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', 422, $validator->errors());
            }

            $result = $this->verificationCodeService->send($request->email);
            return $this->respondFromPayload($result);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function verifyCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'verification_code' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation error', 422, $validator->errors());
            }

            $result = $this->verificationCodeService->verify($request->verification_code);
            return $this->respondFromPayload($result);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    private function respondFromPayload(array $result)
    {
        if ($result['status'] >= 400) {
            return $this->error($result['payload']['error'] ?? 'error', $result['status']);
        }

        return $this->success(null, $result['payload']['message'] ?? 'success', $result['status']);
    }
}
