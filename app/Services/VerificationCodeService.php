<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\VerificationCode;
use Carbon\Carbon;

class VerificationCodeService
{
    public function __construct(private MailService $mailService)
    {
    }

    public function send(string $email): array
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($user->email_verified_at !== null) {
            return [
                'status' => 422,
                'payload' => ['error' => 'This email is already verified.'],
            ];
        }

        VerificationCode::where('user_id', $user->id)->delete();
        $verificationCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(5);

        VerificationCode::create([
            'user_id' => $user->id,
            'verification_code' => $verificationCode,
            'expires_at' => $expiresAt,
        ]);

        try {
            $this->mailService->queue($user->email, new VerificationCodeMail($user, $verificationCode));
        } catch (\Throwable $exception) {
            VerificationCode::where('user_id', $user->id)->delete();
            throw $exception;
        }

        return [
            'status' => 200,
            'payload' => [
                'message' => 'Verification code sent successfully.',
                'expires_at' => $expiresAt->toDateTimeString(),
            ],
        ];
    }

    public function verify(string $code): array
    {
        $verificationCode = VerificationCode::where('verification_code', $code)->first();

        if (!$verificationCode) {
            return [
                'status' => 400,
                'payload' => ['error' => 'Invalid verification code.'],
            ];
        }

        $user = User::find($verificationCode->user_id);

        if (!$user) {
            return [
                'status' => 400,
                'payload' => ['error' => 'User not found.'],
            ];
        }

        if (Carbon::now()->greaterThan($verificationCode->expires_at)) {
            return [
                'status' => 400,
                'payload' => ['error' => 'Verification code expired.'],
            ];
        }

        $user->email_verified_at = now();
        $user->save();
        $verificationCode->delete();

        return [
            'status' => 200,
            'payload' => ['message' => 'Verification successful.'],
        ];
    }
}
