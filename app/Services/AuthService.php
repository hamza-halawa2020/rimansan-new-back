<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Api\VerificationCodeController;
use App\Traits\ApiResponse;

class AuthService
{
    use ApiResponse;

     protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function login(array $credentials)
    {
        $user = null;

        if (filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
            $user = Auth::attempt(['email' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
        } else {
            $user = Auth::attempt(['phone' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
        }

        if (!$user) {
            return $this->error(
                filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)
                    ? 'Invalid email or password'
                    : 'Invalid phone number or password',
                401
            );
        }

        $user = Auth::user();

        if (!$user->email_verified_at) {
            return $this->error('User not verified', 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken($user->phone);

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'slug'  => $user->slug,
            'email' => $user->email,
            'image' => $user->image,
            'phone' => $user->phone,
            'token' => $token->plainTextToken
        ]);
    }

    public function adminLogin(array $credentials)
    {
        $response = $this->login($credentials);

   
        $user = Auth::user();

        if ($user->type !== 'admin') {
            return $this->error('User not admin', 403);
        }

        return response()->json($response->original);
    }

    public function register(array $validatedData, $imageFile = null)
    {
        DB::beginTransaction();
        try {
            $filename = null;

            if ($imageFile) {
                $data['image'] = $this->fileService->upload($imageFile, 'images/users');
            }

            $user = User::create([
                'name'     => $validatedData['name'],
                'phone'    => $validatedData['phone'],
                'email'    => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'slug'     => Str::slug($validatedData['name']) . '-' . rand(1, 999),
                'image'    => $filename ?? 'default.png',
            ]);

            $verificationSent = app(VerificationCodeController::class)
                ->sendVerificationCode(new Request(['email' => $user->email]));

            if (!$verificationSent) {
                throw new Exception("Failed to send verification email");
            }

            DB::commit();
            return $this->success(new UserResource($user), 'Registration successful', 201);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    public function logout($user)
    {
        $user->tokens()->delete();
        return $this->success(null, 'Logged out successfully', 200);
    }
}
