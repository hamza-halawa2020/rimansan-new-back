<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show', 'randomPosts']);
        $this->middleware("limitReq");
    }

    public function index()
    {
        try {
            $Posts = Post::paginate(10);
            return PostResource::collection($Posts);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    public function randomPosts()
    {
        try {
            $Posts = Post::inRandomOrder()->take(3)->get();
            return PostResource::collection($Posts);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StorePostRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $adminId = auth()->id();
            $validatedData['admin_id'] = $adminId;
            if (Gate::allows("is-admin")) {

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/posts/';
                    $image->move(public_path($folderPath), $filename);
                }

                $validatedData['image'] = $filename ?? 'default.png';

                $Post = Post::create($validatedData);
                return response()->json(['data' => new PostResource($Post)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store Post.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Post = Post::findOrFail($id);
            return new PostResource($Post);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdatePostRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();

            if (!Gate::allows("is-admin")) {
                return response()->json(['message' => 'Not allowed to update Post.'], 403);
            }

            $Post = Post::findOrFail($id);
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $folderPath = 'images/posts/';

                if ($Post->image && $Post->image !== 'images/social/default.png' && file_exists(public_path($Post->image))) {
                    unlink(public_path($Post->image));
                }

                $image->move(public_path($folderPath), $filename);
                $validatedData['image'] = $filename;
            }

            $Post->update($validatedData);
            return response()->json(['data' => new PostResource($Post)], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Post = Post::findOrFail($id);

                if ($Post->image && $Post->image !== 'images/posts/default.png' && file_exists(public_path($Post->image))) {
                    unlink(public_path($Post->image));
                }

                $Post->delete();
                return response()->json(['data' => 'Post deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete Post.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
