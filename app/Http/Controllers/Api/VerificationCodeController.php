<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VerificationCode;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerificationCodeController extends Controller
{

    function __construct()
    {
        $this->middleware("limitReq");
    }

    public function sendVerificationCode(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $user = User::where('email', $request->email)->first();
            if ($user->email_verified_at != null) {
                return response()->json(['error' => 'This email is already verified.'], 422);
            }

            VerificationCode::where('user_id', $user->id)->delete();
            $verificationCode = rand(100000, 999999);
            $expiresAt = Carbon::now()->addMinutes(5);
            VerificationCode::create([
                'user_id' => $user->id,
                'verification_code' => $verificationCode,
                'expires_at' => $expiresAt,
            ]);

             // Send via WhatsApp if phone is provided
             if ($request->phone || $user->phone) {
                $phone = str_replace(['+', ' '], '', ($request->phone ?? $user->phone));
                $response = Http::withHeaders([
                    'X-API-Token' => config('services.whatsapp.token'),
                    'Accept' => 'application/json',
                ])->post(config('services.whatsapp.url'), [
                    'phone' => $phone,
                    'message' => "Your verification code is: $verificationCode. It is valid for 5 minutes.",
                ]);

                if ($response->failed()) {
                    Log::error('WhatsApp API error: ' . json_encode($response->json()));
                    // Continue to email as fallback
                }
            }

            Mail::send('emails.verification_code', [
                'user' => $user,
                'verificationCode' => $verificationCode,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verification Code');
            });


            return response()->json([
                'message' => 'Verification code sent successfully.',
                'expires_at' => $expiresAt->toDateTimeString(),
            ]);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function verifyCode(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'verification_code' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $verificationCode = VerificationCode::where('verification_code', $request->verification_code)->first();

            if (!$verificationCode) {
                return response()->json(['error' => 'Invalid verification code.'], 400);
            }
            $user = User::find($verificationCode->user_id);

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 400);
            }
            if (Carbon::now()->greaterThan($verificationCode->expires_at)) {
                return response()->json(['error' => 'Verification code expired.'], 400);
            }
            $user->email_verified_at = now();
            $user->save();
            $verificationCode->delete();

            return response()->json(['message' => 'Verification successful.']);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

}
