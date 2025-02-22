<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveProductReviewRequest;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\UpdateProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\Client;
use App\Models\ProductReview;
use Exception;
use Illuminate\Support\Facades\Gate;

class ProductReviewController extends Controller
{

    function __construct()
    {
        $this->middleware("auth:sanctum")->only(['all', 'showAll', 'active', 'update', 'destroy', 'store']);
        $this->middleware("limitReq");

    }

    public function index()
    {
        try {
            $reviews = ProductReview::where('status', 'active')->orderBy('created_at', 'desc')->get();
            return ProductReviewResource::collection($reviews);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function all()
    {
        try {
            if (Gate::allows("is-admin")) {
                $reviews = ProductReview::paginate(10);
                return ProductReviewResource::collection($reviews);
            } else {
                return response()->json(['message' => 'not allow .'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreProductReviewRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $userId = auth()->id();
            $validatedData['user_id'] = $userId;

            $review = ProductReview::create($validatedData);

            return response()->json([
                'message' => 'Your review submitted but not activated yet.',
                'data' => new ProductReviewResource($review),
            ], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function storeByClient(StoreProductReviewRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $client = Client::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
            ]);
            $validatedData['client_id'] = $client->id;
            $review = ProductReview::create($validatedData);
            return response()->json([
                'message' => 'Your review submitted but not activated yet.',
                'data' => new ProductReviewResource($review),
            ], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }



    public function show(string $id)
    {
        try {
            $review = ProductReview::where('status', 'active')->findOrFail($id);
            return new ProductReviewResource($review);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function showAll(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $review = ProductReview::findOrFail($id);
                return new ProductReviewResource($review);
            } else {
                return response()->json(['message' => 'not allow to delete review.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function active(ActiveProductReviewRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();
                $adminId = auth()->id();
                $validatedData['admin_id'] = $adminId;
                $review = ProductReview::findOrFail($id);
                $review->update($validatedData);
                return response()->json(['data' => new ProductReviewResource($review)], 200);
            } else {
                return response()->json(['message' => 'not allow to active review.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function update(UpdateProductReviewRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $userId = auth()->id();
            $review = ProductReview::findOrFail($id);

            if ($review->user_id !== $userId) {
                return response()->json(['message' => 'You are not the owner of this review.'], 403);
            }
            $review->update($validatedData);
            return response()->json(['data' => new ProductReviewResource($review)], 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $user = ProductReview::findOrFail($id);
                $user->delete();
                return response()->json(['data' => 'review deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete review.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
