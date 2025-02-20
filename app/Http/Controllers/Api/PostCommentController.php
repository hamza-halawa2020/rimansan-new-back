<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivePostCommentRequest;
use App\Http\Requests\StorePostCommentRequest;
use App\Http\Requests\UpdatePostCommentRequest;
use App\Http\Resources\PostCommentResource;
use App\Models\PostComment;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{

    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

    }


    public function index()
    {
        try {
            $comments = PostComment::where('status', 'active')->all();
            return PostCommentResource::collection($comments);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function all(Request $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $postId = $request->query('post_id'); // Get post_id from query string
                $query = PostComment::query();

                if ($postId) {
                    $query->where('post_id', $postId); // Filter by post_id if provided
                }

                $comments = $query->paginate(10);
                return PostCommentResource::collection($comments);
            } else {
                return response()->json(['message' => 'Not allowed to view comments.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function store(StorePostCommentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = $this->userId;
            $comment = PostComment::create($validatedData);
            return response()->json([
                'message' => 'Your comment submitted but not activated yet .',
                'data' => new PostCommentResource($comment)
            ], 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }



    public function show(string $id)
    {
        try {
            $comment = PostComment::where('status', 'active')->findOrFail($id);
            return new PostCommentResource($comment);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function showAll(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $comment = PostComment::findOrFail($id);
                return new PostCommentResource($comment);
            } else {
                return response()->json(['message' => 'not allow to delete comment.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function active(ActivePostCommentRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();
                $validatedData['admin_id'] = $this->userId;
                $comment = PostComment::findOrFail($id);
                $comment->update($validatedData);
                return response()->json(['data' => new PostCommentResource($comment)], 200);
            } else {
                return response()->json(['message' => 'not allow to active comment.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function update(UpdatePostCommentRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $comment = PostComment::findOrFail($id);

            if ($comment->user_id !== $this->userId) {
                return response()->json(['message' => 'You are not the owner of this comment.'], 403);
            }
            $comment->update($validatedData);
            return response()->json(['data' => new PostCommentResource($comment)], 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $user = PostComment::findOrFail($id);
                $user->delete();
                return response()->json(['data' => 'comment deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete comment.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
