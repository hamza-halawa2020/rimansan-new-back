<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UserService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        return User::all();
    }

    public function store(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => bcrypt('12345678'),
            'slug' => Str::slug($data['name']),
            'image' => 'default.png',
        ]);
    }

    public function show(string $id)
    {
        return User::findOrFail($id);
    }

    public function profile(int $userId)
    {
        return User::with('addresses', 'points')->findOrFail($userId);
    }

    public function updateSelf(array $data, string $id)
    {
        $user = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            $this->deleteImage($user->image);
            $data['image'] = $this->fileService->upload($data['image'], 'images/users');
        }

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? $user->phone,
            'type' => $user->type,
            'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
            'image' => $data['image'] ?? $user->image,
        ]);

        return $user;
    }

    public function updateByAdmin(array $data, string $id)
    {
        $user = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            $this->deleteImage($user->image);
            $data['image'] = $this->fileService->upload($data['image'], 'images/users');
        }

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'phone' => $data['phone'] ?? $user->phone,
            'email' => $data['email'] ?? $user->email,
            'type' => $data['type'] ?? $user->type,
            'password' => $user->password,
            'image' => $data['image'] ?? $user->image,
        ]);

        return $user;
    }

    public function destroy(string $id)
    {
        $user = $this->show($id);
        $user->delete();
        return $user;
    }

    private function deleteImage(?string $image): void
    {
        if (!$image || $image === 'default.png' || $image === 'images/users/default.png') {
            return;
        }

        $path = str_contains($image, '/') ? $image : 'images/users/' . $image;
        $this->fileService->delete($path);
    }
}
