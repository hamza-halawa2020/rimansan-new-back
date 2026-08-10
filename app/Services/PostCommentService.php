<?php

namespace App\Services;

use App\Models\PostComment;

class PostCommentService
{
    public function index()
    {
        return PostComment::where('status', 'active')->orderBy('created_at', 'desc')->get();
    }

    public function all(?string $postId)
    {
        $query = PostComment::query();

        if ($postId) {
            $query->where('post_id', $postId);
        }

        return $query->paginate(10);
    }

    public function store(array $data, int $userId)
    {
        $data['user_id'] = $userId;
        return PostComment::create($data);
    }

    public function show(string $id)
    {
        return PostComment::where('status', 'active')->findOrFail($id);
    }

    public function showAll(string $id)
    {
        return PostComment::findOrFail($id);
    }

    public function active(array $data, string $id, int $adminId)
    {
        $data['admin_id'] = $adminId;
        $comment = $this->showAll($id);
        $comment->update($data);
        return $comment;
    }

    public function update(array $data, string $id)
    {
        $comment = $this->showAll($id);
        $comment->update($data);
        return $comment;
    }

    public function destroy(string $id)
    {
        $comment = $this->showAll($id);
        $comment->delete();
        return $comment;
    }
}
