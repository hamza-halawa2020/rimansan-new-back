<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Exception;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{

    private $userId;
    function __construct()
    {
        $this->middleware("auth:sanctum")->except('index', 'show');
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $Courses = Course::all();
            return CourseResource::collection($Courses);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreCourseRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $validatedData['admin_id'] = $this->userId;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/Courses/';
                    $image->move(public_path($folderPath), $filename);
                }
                $validatedData['image'] = $filename ?? 'default.png';
                $Course = Course::create($validatedData);
                return response()->json(['data' => new CourseResource($Course),], 201);
            }
            return response()->json(['message' => 'Not allowed to store Courses.'], 403);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to add Course. Please try again later.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Course = Course::findOrFail($id);
            return new CourseResource($Course);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateCourseRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Course = Course::find($id);

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/courses/';

                    if ($Course->image && $Course->image !== 'images/Courses/default.png' && file_exists(public_path($Course->image))) {
                        unlink(public_path($Course->image));
                    }

                    $image->move(public_path($folderPath), $filename);
                    $validatedData['image'] = $folderPath . $filename;
                }

                $validatedData = $request->validated();
                $Course->update($validatedData);
                return response()->json(new CourseResource($Course), 200);
            }
            return response()->json(['message' => 'Not allowed to update Courses.'], 403);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the Course.'], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Course = Course::findOrFail($id);
                $Course->delete();
                return response()->json(['data' => 'Course deleted successfully'], 200);
            }
            return response()->json(['message' => 'Not allowed to update Courses.'], 403);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
