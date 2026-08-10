<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Exception;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use App\Traits\ApiResponse;

class ProductController extends Controller
{
    use ApiResponse;

    private ProductService $productService;

    function __construct(ProductService $productService)
    {
        $this->middleware("auth:sanctum")->except(["index", "show", "indexByCategory"]);
        $this->productService = $productService;
    }

    public function index()
    {
        try {
            $products = $this->productService->index();
            return $this->success(ProductResource::collection($products));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function indexByCategory($id)
    {
        try {
            $products = $this->productService->indexByCategory($id);
            return $this->success(ProductResource::collection($products));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                if ($request->hasFile('image')) {
                    $validatedData['image'] = $request->file('image');
                }
                $adminId = auth()->id();
                $validatedData['admin_id'] = $adminId;
                $product = $this->productService->store($validatedData);
                return $this->success(new ProductResource($product), 'success', 201);
            } else {
                return $this->error('not allow to delete product.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $product = $this->productService->show($id);
            return $this->success(new ProductResource($product));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                if ($request->hasFile('image')) {
                    $validatedData['image'] = $request->file('image');
                }
                $product = $this->productService->update($validatedData, $id);
                return $this->success(new ProductResource($product));
            } else {
                return $this->error('not allowed to update product.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function deleteImage(string $productId, string $imageId)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->productService->deleteImage($productId, $imageId);
                return $this->success(null, 'Image deleted successfully');
            }

            return $this->error('Unauthorized', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->productService->destroy($id);
                return $this->success(null, 'product deleted successfully');
            } else {
                return $this->error('not allow to delete product.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
