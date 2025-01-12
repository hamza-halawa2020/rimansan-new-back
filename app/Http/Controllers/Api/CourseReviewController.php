<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveCourseReviewRequest;
use App\Http\Requests\StoreCourseReviewRequest;
use App\Http\Requests\UpdateCourseReviewRequest;
use App\Http\Resources\CourseReviewResource;
use App\Models\CourseReview;
use Exception;
use Illuminate\Support\Facades\Gate;

class CourseReviewController extends Controller
{
    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum")->only(['all', 'showAll', 'active', 'update', 'destroy', 'store']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

    }

    public function index()
    {
        try {
            $reviews = CourseReview::where('status', 'active')->paginate(10);
            return CourseReviewResource::collection($reviews);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function all()
    {
        try {
            if (Gate::allows("is-admin")) {
                $reviews = CourseReview::paginate(10);
                return CourseReviewResource::collection($reviews);
            } else {
                return response()->json(['message' => 'not allow .'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreCourseReviewRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $validatedData['user_id'] = $this->userId;

            $review = CourseReview::create($validatedData);

            return response()->json([
                'message' => 'Your review submitted but not activated yet.',
                'data' => new CourseReviewResource($review),
            ], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $review = CourseReview::where('status', 'active')->findOrFail($id);
            return new CourseReviewResource($review);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function showAll(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $review = CourseReview::findOrFail($id);
                return new CourseReviewResource($review);
            } else {
                return response()->json(['message' => 'not allow to delete review.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function active(ActiveCourseReviewRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();
                $validatedData['admin_id'] = $this->userId;
                $review = CourseReview::findOrFail($id);
                $review->update($validatedData);
                return response()->json(['data' => new CourseReviewResource($review)], 200);
            } else {
                return response()->json(['message' => 'not allow to active review.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function update(UpdateCourseReviewRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $userId = auth()->id();
            $review = CourseReview::findOrFail($id);

            if ($review->user_id !== $userId) {
                return response()->json(['message' => 'You are not the owner of this review.'], 403);
            }
            $review->update($validatedData);
            return response()->json(['data' => new CourseReviewResource($review)], 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $user = CourseReview::findOrFail($id);
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
