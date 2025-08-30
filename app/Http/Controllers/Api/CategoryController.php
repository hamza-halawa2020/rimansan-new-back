<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Exception;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    use ApiResponse;

    private CategoryService $categoryService;

    function __construct(CategoryService $categoryService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        try {
            $categories =  $this->categoryService->index();
            return $this->success(CategoryResource::collection($categories));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {

                $category = $this->categoryService->store($validatedData);
                return $this->success(new CategoryResource($category), 200);
            } else {
                return $this->error('not allow to store category.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $category = $this->categoryService->show($id);
            return $this->success(new CategoryResource($category));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {

                $category = $this->categoryService->update($validatedData, $id);
                return $this->success(new CategoryResource($category), 200);
            } else {
                return $this->error('not allow to update category.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $user = $this->categoryService->destroy($id);
                return $this->success('Category deleted $this->fully', 200);
            } else {
                return $this->error('not allow to delete Category.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
