<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    use ApiResponse;
    private $userId;
    private CourseService $courseService;

    function __construct(CourseService $courseService)
    {
        $this->middleware("auth:sanctum")->except('index', 'show', 'randomCourses');
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->courseService = $courseService;
    }

    public function index()
    {
        try {
            $Courses = $this->courseService->index();
            return $this->success(CourseResource::collection($Courses));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function randomCourses()
    {
        try {
            $randomCourses = $this->courseService->randomCourses();
            return $this->success(CourseResource::collection($randomCourses));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function store(StoreCourseRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $validatedData['admin_id'] = $this->userId;
                $course = $this->courseService->store($validatedData);
                return $this->success(new CourseResource($course), 201);
            }
            return $this->error('Not allowed to store Courses.', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Course = $this->courseService->show($id);
            return $this->success(new CourseResource($Course));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCourseRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to update course.', 403);
            }
            $course = $this->courseService->update($validatedData, $id);
            return $this->success(new CourseResource($course), 'Course updated successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Course = $this->courseService->destroy($id);
                return $this->success(new CourseResource($Course), 'Course deleted successfully');
            }
            return $this->error('Not allowed to delete Courses.', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
