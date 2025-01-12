<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use App\Mail\PasswordResetMail;
use App\Models\ResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgetPasswordController extends Controller
{

    function __construct()
    {
        $this->middleware("limitReq");
    }
    public function forgotPassword(ForgetPasswordRequest $request)
    {
        try {
            $credentials = $request->only('emailOrPhone');
            $user = null;

            if (filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $credentials['emailOrPhone'])->first();
            } else {
                $user = User::where('phone', $credentials['emailOrPhone'])->first();
            }

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $token = Str::random(60);
            $expiresAt = Carbon::now()->addMinutes(5);
            ResetPassword::where('user_id', $user->id)->delete();
            ResetPassword::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'expires_at' => $expiresAt,
                    'token' => hash('sha256', $token),
                ]
            );

            Mail::to($user->email)->send(new PasswordResetMail($token));

            return response()->json(['message' => 'Password reset link has been sent'], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $this->validate($request, [
                'token' => 'required|string',
                'password' => 'required|string|confirmed|min:8',
            ]);
            $hashedToken = hash('sha256', $request->token);
            $reset = ResetPassword::where('token', $hashedToken)->first();
            if (!$reset) {
                return response()->json(['message' => 'Token not found in the database'], 400);
            }
            if (Carbon::now()->greaterThan($reset->expires_at)) {
                return response()->json(['message' => 'Token has expired'], 400);
            }
            $user = User::find($reset->user_id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user->password = bcrypt($request->password);
            $user->save();
            $reset->delete();

            return response()->json(['message' => 'Password has been reset successfully'], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }



}
