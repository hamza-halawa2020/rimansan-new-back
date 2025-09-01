<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveCourseReviewRequest;
use App\Http\Requests\StoreCourseReviewRequest;
use App\Http\Requests\UpdateCourseReviewRequest;
use App\Http\Resources\CourseReviewResource;
use App\Models\CourseReview;
use App\Services\CourseReviewService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class CourseReviewController extends Controller
{
    use ApiResponse;
    private $userId;

    private CourseReviewService $courseReviewService;

    function __construct(CourseReviewService $courseReviewService)
    {
        $this->middleware("auth:sanctum")->only(['all', 'showAll', 'active', 'update', 'destroy', 'store']);
        $this->middleware("limitReq");
        $this->courseReviewService = $courseReviewService;
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $reviews =  $this->courseReviewService->index();
            return $this->success(CourseReviewResource::collection($reviews));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function all()
    {
        try {
            if (Gate::allows("is-admin")) {
                $reviews =  $this->courseReviewService->all();
                return $this->success(CourseReviewResource::collection($reviews));
            } else {
                return $this->success('not allow to view all reviews.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreCourseReviewRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = $this->userId;
            $review = CourseReview::create($validatedData);
            return $this->success(new CourseReviewResource($review), 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $review = $this->courseReviewService->show($id);
            return $this->success(new CourseReviewResource($review));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function showAll(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $review = $this->courseReviewService->showAll($id);
                return $this->success(new CourseReviewResource($review));
            } else {
                return $this->error('not allow to show review.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function active(ActiveCourseReviewRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $validatedData['admin_id'] = $this->userId;
                $review = $this->courseReviewService->active($validatedData, $id);
                return $this->success(new CourseReviewResource($review), 200);
            } else {
                return $this->success('not allow to active review.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function update(UpdateCourseReviewRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $userId = auth()->id();
            $review = $this->courseReviewService->update($validatedData, $id);
            if ($review->user_id !== $userId) {
                return $this->success('You are not the owner of this review.', 403);
            }
            $review->update($validatedData);
            return $this->success(new CourseReviewResource($review), 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $review = $this->courseReviewService->destroy($id);
                return $this->success('review deleted successfully', 200);
            } else {
                return $this->success('not allow to delete review.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
