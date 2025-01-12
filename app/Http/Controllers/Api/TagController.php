<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Exception;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");

    }

    public function index()
    {
        try {
            $tags = Tag::all();
            return TagResource::collection($tags);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $tag = Tag::create($validatedData);
                return response()->json(['data' => new TagResource($tag)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store tag.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        try {
            $tag = Tag::findOrFail($id);
            return new TagResource($tag);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $tag = Tag::findOrFail($id);
                $tag->update($validatedData);
                return response()->json(['data' => new TagResource($tag)], 200);
            } else {
                return response()->json(['message' => 'not allow to update tag.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $user = Tag::findOrFail($id);
                $user->delete();
                return response()->json(['data' => 'tag deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete tag.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
