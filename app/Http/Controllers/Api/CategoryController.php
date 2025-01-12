<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
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
            $categories = Category::all();
            return CategoryResource::collection($categories);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $category = Category::create($validatedData);
                return response()->json(['data' => new CategoryResource($category)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store category.'], 403);
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
            $category = Category::findOrFail($id);
            return new CategoryResource($category);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $category = Category::findOrFail($id);
                $category->update($validatedData);
                return response()->json(['data' => new CategoryResource($category)], 200);
            } else {
                return response()->json(['message' => 'not allow to update category.'], 403);
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
                $user = Category::findOrFail($id);
                $user->delete();
                return response()->json(['data' => 'Category deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete Category.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
