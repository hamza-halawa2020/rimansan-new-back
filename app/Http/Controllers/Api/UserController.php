<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $users = User::all();
                return UserResource::collection($users);
            } else {
                return response()->json(['message' => 'not allow to show users.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();

                $user = User::create([
                    'name' => $validatedData['name'],
                    'phone' => $validatedData['phone'],
                    'email' => $validatedData['email'],
                    'password' => bcrypt('12345678'),
                    'slug' => Str::slug($validatedData['name']),
                    'image' => 'default.png',
                ]);

                $verificationSent = app(VerificationCodeController::class)
                ->sendVerificationCode(new Request(['email' => $user->email]));

                if (!$verificationSent) {
                    throw new Exception("Failed to send verification email");
                }

                return response()->json(['data' => new UserResource($user)], 200);
            } else {
                return response()->json(['message' => 'not allow to show users.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);
            return new UserResource($user);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function profile()
    {
        try {
            $user = User::with('addresses','points')->findOrFail($this->userId);
            return new UserResource($user);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $user = User::findOrFail($id);
            $data = $request->validated();

            if (auth()->user()->id === (int) $id) {

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $folderPath = 'images/users/';
                    $image->move(public_path($folderPath), $filename);

                    if ($user->image && file_exists(public_path($folderPath . $user->image))) {
                        unlink(public_path($folderPath . $user->image));
                    }

                    $data['image'] = $filename;
                }


                $user->update([
                    'name' => $data['name'] ?? $user->name,
                    'email' => $data['email'] ?? $user->email,
                    'phone' => $data['phone'] ?? $user->phone,
                    'type' => $user->type,
                    'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
                    'image' => $data['image'] ?? $user->image,
                ]);


                return response()->json(['data' => new UserResource($user)], 200);
            } else if (Gate::allows('is-admin')) {

                $user->update([
                    'name' => $data['name'] ?? $user->name,
                    'phone' => $data['phone'] ?? $user->phone,
                    'email' => $data['email'] ?? $user->email,
                    'type' => $data['type'] ?? $user->type,
                    'password' => $user->password,
                    'image' => $user->image,
                ]);


                return response()->json(['data' => new UserResource($user)], 200);
            } else {
                return response()->json(['message' => 'Not authorized to update this user.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $user = User::findOrFail($id);
                $user->delete();
                return response()->json(['data' => 'user deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete user.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
