<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivePostCommentRequest;
use App\Http\Requests\StorePostCommentRequest;
use App\Http\Requests\UpdatePostCommentRequest;
use App\Http\Resources\PostCommentResource;
use App\Services\PostCommentService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    use ApiResponse;

    private $userId;
    private PostCommentService $postCommentService;

    function __construct(PostCommentService $postCommentService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->postCommentService = $postCommentService;
    }


    public function index()
    {
        try {
            $comments = $this->postCommentService->index();
            return $this->success(PostCommentResource::collection($comments));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function all(Request $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $comments = $this->postCommentService->all($request->query('post_id'));
                return $this->success(PostCommentResource::collection($comments));
            } else {
                return $this->error('Not allowed to view comments.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function store(StorePostCommentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $comment = $this->postCommentService->store($validatedData, $this->userId);
            return $this->success(new PostCommentResource($comment), 'Your comment submitted but not activated yet .');

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }



    public function show(string $id)
    {
        try {
            $comment = $this->postCommentService->show($id);
            return $this->success(new PostCommentResource($comment));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function showAll(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $comment = $this->postCommentService->showAll($id);
                return $this->success(new PostCommentResource($comment));
            } else {
                return $this->error('not allow to delete comment.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function active(ActivePostCommentRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();
                $comment = $this->postCommentService->active($validatedData, $id, $this->userId);
                return $this->success(new PostCommentResource($comment));
            } else {
                return $this->error('not allow to active comment.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function update(UpdatePostCommentRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $comment = $this->postCommentService->showAll($id);

            if ($comment->user_id !== $this->userId) {
                return $this->error('You are not the owner of this comment.', 403);
            }
            $comment = $this->postCommentService->update($validatedData, $id);
            return $this->success(new PostCommentResource($comment));

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->postCommentService->destroy($id);
                return $this->success(null, 'comment deleted successfully');
            } else {
                return $this->error('not allow to delete comment.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
