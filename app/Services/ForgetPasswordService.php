<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgetPasswordService
{
    public function generateResetToken(string $emailOrPhone): void
    {
        if (filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $emailOrPhone)->first();
        } else {
            $user = User::where('phone', $emailOrPhone)->first();
        }

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        $token = Str::random(60);
        $expiresAt = Carbon::now()->addMinutes(5);

        ResetPassword::where('user_id', $user->id)->delete();

        ResetPassword::create([
            'user_id' => $user->id,
            'expires_at' => $expiresAt,
            'token' => hash('sha256', $token),
        ]);

        Mail::to($user->email)->queue(new PasswordResetMail($token));
    }


    public function resetPassword(string $token, string $password): void
    {
        $hashedToken = hash('sha256', $token);

        $reset = ResetPassword::where('token', $hashedToken)->first();

        if (!$reset) {
            throw new Exception('Token not found in the database', 400);
        }

        if (Carbon::now()->greaterThan($reset->expires_at)) {
            throw new Exception('Token has expired', 400);
        }

        $user = User::find($reset->user_id);

        if (!$user) {
            throw new Exception('User not found', 404);
        }

        $user->update(['password' => bcrypt($password)]);

        $reset->delete();
    }
}
