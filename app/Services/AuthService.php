<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;

class AuthService
{
    use ApiResponse;

    protected FileService $fileService;
    protected VerificationCodeService $verificationCodeService;

    public function __construct(FileService $fileService, VerificationCodeService $verificationCodeService)
    {
        $this->fileService = $fileService;
        $this->verificationCodeService = $verificationCodeService;
    }

    public function login(array $credentials)
    {
        $authenticated = $this->attemptLogin($credentials);

        if (!$authenticated) {
            return $this->error(
                filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)
                    ? 'Invalid email or password'
                    : 'Invalid phone number or password',
                401
            );
        }

        $user = Auth::user();

        if (!$user) {
            return $this->error('Invalid credentials', 401);
        }

        return $this->loginResponse($user);
    }

    public function adminLogin(array $credentials)
    {
        $authenticated = $this->attemptLogin($credentials);

        if (!$authenticated) {
            return $this->error(
                filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)
                    ? 'Invalid email or password'
                    : 'Invalid phone number or password',
                401
            );
        }

        $user = Auth::user();

        if (!$user) {
            return $this->error('Invalid credentials', 401);
        }

        if ($user->type !== 'admin') {
            Auth::logout();
            return $this->error('User not admin', 403);
        }

        return $this->loginResponse($user);
    }

    private function attemptLogin(array $credentials): bool
    {
        if (filter_var($credentials['emailOrPhone'], FILTER_VALIDATE_EMAIL)) {
            return Auth::attempt(['email' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
        }

        return Auth::attempt(['phone' => $credentials['emailOrPhone'], 'password' => $credentials['password']]);
    }

    private function loginResponse(User $user)
    {
        if (!$user->email_verified_at) {
            return $this->error('User not verified', 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken($user->phone);

        return $this->success([
            'id'    => $user->id,
            'name'  => $user->name,
            'slug'  => $user->slug,
            'email' => $user->email,
            'image' => $user->image,
            'phone' => $user->phone,
            'token' => $token->plainTextToken
        ]);
    }

    public function register(array $validatedData, $imageFile = null)
    {
        DB::beginTransaction();
        try {
            $filename = null;

            if ($imageFile) {
                $filename = $this->fileService->upload($imageFile, 'images/users');
            }

            $user = User::create([
                'name'     => $validatedData['name'],
                'phone'    => $validatedData['phone'],
                'email'    => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'slug'     => Str::slug($validatedData['name']) . '-' . rand(1, 999),
                'image'    => $filename ?? 'default.png',
            ]);

            $verificationSent = $this->verificationCodeService->send($user->email);

            if ($verificationSent['status'] !== 200) {
                throw new Exception("Failed to send verification email");
            }

            DB::commit();
            return $this->success(new UserResource($user), 'Registration successful', 201);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function logout($user)
    {
        $user->tokens()->delete();
        return $this->success(null, 'Logged out successfully', 200);
    }
}
