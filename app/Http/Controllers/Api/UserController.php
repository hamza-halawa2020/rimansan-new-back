<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use App\Services\VerificationCodeService;
use App\Traits\ApiResponse;
use Exception;

class UserController extends Controller
{
    use ApiResponse;

    private $userId;
    private UserService $userService;
    private VerificationCodeService $verificationCodeService;

    function __construct(UserService $userService, VerificationCodeService $verificationCodeService)
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->userService = $userService;
        $this->verificationCodeService = $verificationCodeService;
    }

    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $users = $this->userService->index();
                return $this->success(UserResource::collection($users));
            } else {
                return $this->error('not allow to show users.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();

                $user = $this->userService->store($validatedData);

                $verificationSent = $this->verificationCodeService->send($user->email);
                if ($verificationSent['status'] !== 200) {
                    throw new Exception("Failed to send verification email");
                }

                return $this->success(new UserResource($user));
            } else {
                return $this->error('not allow to show users.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $user = $this->userService->show($id);
            return $this->success(new UserResource($user));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function profile()
    {
        try {
            $user = $this->userService->profile($this->userId);
            return $this->success(new UserResource($user));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            if (auth()->user()->id === (int) $id) {
                $user = $this->userService->updateSelf($data, $id);
                return $this->success(new UserResource($user));
            } else if (Gate::allows('is-admin')) {
                $user = $this->userService->updateByAdmin($data, $id);
                return $this->success(new UserResource($user));
            } else {
                return $this->error('Not authorized to update this user.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->userService->destroy($id);
                return $this->success(null, 'user deleted successfully');
            } else {
                return $this->error('not allow to delete user.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
