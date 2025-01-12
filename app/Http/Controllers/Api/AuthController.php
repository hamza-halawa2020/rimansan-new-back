<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    function __construct()
    {
        $this->middleware("auth:sanctum")->only('logout');
        $this->middleware("limitReq");
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('emailOrPhone', 'password');
            $user = null;

            if (filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
                $user = Auth::attempt(['email' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
            } else {
                $user = Auth::attempt(['phone' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
            }

            if (!$user) {
                if (filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
                    return response()->json(['message' => 'Invalid email or password'], 401);
                } else {
                    return response()->json(['message' => 'Invalid phone number or password'], 401);
                }
            }
            $user = Auth::user();
            if (!$user->email_verified_at) {
                return response()->json(['message' => 'User not verified'], 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken($user->phone);
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
                'email' => $user->email,
                'image' => $user->image,
                'phone' => $user->phone,
                'token' => $token->plainTextToken
            ], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function AdminLogin(LoginRequest $request)
    {
        try {
            $credentials = $request->only('emailOrPhone', 'password');
            $user = null;

            if (filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
                $user = Auth::attempt(['email' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
            } else {
                $user = Auth::attempt(['phone' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
            }

            if (!$user) {
                if (filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
                    return response()->json(['message' => 'Invalid email or password'], 401);
                } else {
                    return response()->json(['message' => 'Invalid phone number or password'], 401);
                }
            }
            $user = Auth::user();
            if (!$user->email_verified_at) {
                return response()->json(['message' => 'User not verified'], 401);
            }
            if ($user->type != 'admin') {
                return response()->json(['message' => 'User not admin'], 403);
            }


            $user->tokens()->delete();
            $token = $user->createToken($user->phone);
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
                'email' => $user->email,
                'image' => $user->image,
                'phone' => $user->phone,
                'token' => $token->plainTextToken
            ], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        $filename = null;
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('image')) {

                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $folderPath = 'images/users/';
                $image->move(public_path($folderPath), $filename);
            }
            $user = User::create([
                'name' => $validatedData['name'],
                'phone' => $validatedData['phone'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'slug' => Str::slug($validatedData['name']) . '-' . rand(1, 999),
                'image' => $filename ?? 'default.png',
            ]);
            $verificationSent = app(VerificationCodeController::class)
                ->sendVerificationCode(new Request(['email' => $user->email]));

            if (!$verificationSent) {
                throw new Exception("Failed to send verification email");
            }
            DB::commit();
            return response()->json(['data' => new UserResource($user)], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
