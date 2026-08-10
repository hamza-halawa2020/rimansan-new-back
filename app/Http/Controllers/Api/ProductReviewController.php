<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveProductReviewRequest;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\UpdateProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Services\ProductReviewService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class ProductReviewController extends Controller
{
    use ApiResponse;

    private ProductReviewService $productReviewService;

    function __construct(ProductReviewService $productReviewService)
    {
        $this->middleware("auth:sanctum")->only(['all', 'showAll', 'active', 'update', 'destroy', 'store']);
        $this->middleware("limitReq");
        $this->productReviewService = $productReviewService;
    }

    public function index()
    {
        try {
            $reviews = $this->productReviewService->index();
            return $this->success(ProductReviewResource::collection($reviews));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function all()
    {
        try {
            if (Gate::allows("is-admin")) {
                $reviews = $this->productReviewService->all();
                return $this->success(ProductReviewResource::collection($reviews));
            } else {
                return $this->error('not allow .', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreProductReviewRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $userId = auth()->id();
            $review = $this->productReviewService->store($validatedData, $userId);

            return $this->success(new ProductReviewResource($review), 'Your review submitted but not activated yet.');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function storeByClient(StoreProductReviewRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $review = $this->productReviewService->storeByClient($validatedData);
            return $this->success(new ProductReviewResource($review), 'Your review submitted but not activated yet.');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }



    public function show(string $id)
    {
        try {
            $review = $this->productReviewService->show($id);
            return $this->success(new ProductReviewResource($review));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function showAll(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $review = $this->productReviewService->showAll($id);
                return $this->success(new ProductReviewResource($review));
            } else {
                return $this->error('not allow to delete review.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function active(ActiveProductReviewRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();
                $adminId = auth()->id();
                $review = $this->productReviewService->active($validatedData, $id, $adminId);
                return $this->success(new ProductReviewResource($review));
            } else {
                return $this->error('not allow to active review.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function update(UpdateProductReviewRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $userId = auth()->id();
            $review = $this->productReviewService->showAll($id);

            if ($review->user_id !== $userId) {
                return $this->error('You are not the owner of this review.', 403);
            }
            $review = $this->productReviewService->update($validatedData, $id);
            return $this->success(new ProductReviewResource($review));

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->productReviewService->destroy($id);
                return $this->success(null, 'review deleted successfully');
            } else {
                return $this->error('not allow to delete review.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
