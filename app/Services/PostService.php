<?php

namespace App\Services;

use App\Models\Post;

class PostService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        return Post::all();
    }

    public function randomPosts()
    {
        return Post::inRandomOrder()->take(3)->get();
    }

    public function store(array $data)
    {
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->fileService->upload($data['image'], 'images/posts');
        } else {
            $data['image'] = 'default.png';
        }

        return Post::create($data);
    }

    public function show(string $id)
    {
        return Post::findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $post = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            $this->deleteImage($post->image);
            $data['image'] = $this->fileService->upload($data['image'], 'images/posts');
        }

        $post->update($data);
        return $post;
    }

    public function destroy(string $id)
    {
        $post = $this->show($id);
        $this->deleteImage($post->image);
        $post->delete();
        return $post;
    }

    private function deleteImage(?string $image): void
    {
        if (!$image || $image === 'default.png' || $image === 'images/posts/default.png') {
            return;
        }

        $path = str_contains($image, '/') ? $image : 'images/posts/' . $image;
        $this->fileService->delete($path);
    }
}
