<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    use ApiResponse;

    private PostService $postService;

    function __construct(PostService $postService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show', 'randomPosts']);
        $this->middleware("limitReq");
        $this->postService = $postService;
    }

    public function index()
    {
        try {
            $Posts = $this->postService->index();
            return $this->success(PostResource::collection($Posts));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function randomPosts()
    {
        try {
            $Posts = $this->postService->randomPosts();
            return $this->success(PostResource::collection($Posts));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StorePostRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('image')) {
                $validatedData['image'] = $request->file('image');
            }
            $adminId = auth()->id();
            $validatedData['admin_id'] = $adminId;
            if (Gate::allows("is-admin")) {
                $Post = $this->postService->store($validatedData);
                return $this->success(new PostResource($Post));
            } else {
                return $this->error('not allow to Store Post.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Post = $this->postService->show($id);
            return $this->success(new PostResource($Post));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdatePostRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('image')) {
                $validatedData['image'] = $request->file('image');
            }

            if (!Gate::allows("is-admin")) {
                return $this->error('Not allowed to update Post.', 403);
            }

            $Post = $this->postService->update($validatedData, $id);
            return $this->success(new PostResource($Post));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->postService->destroy($id);
                return $this->success(null, 'Post deleted successfully');
            } else {
                return $this->error('not allow to delete Post.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
