<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstructorRequest;
use App\Http\Requests\UpdateInstructorRequest;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use Exception;
use Illuminate\Support\Facades\Gate;

class InstructorController extends Controller
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
            $Instructors = Instructor::paginate(10);
            return InstructorResource::collection($Instructors);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreInstructorRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $validatedData['admin_id'] = $this->userId;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/instructors/';
                    $image->move(public_path($folderPath), $filename);
                }
                $validatedData['image'] = $filename ?? 'default.png';
                $Instructor = Instructor::create($validatedData);
                return response()->json(['data' => new InstructorResource($Instructor),], 201);
            }
            return response()->json(['message' => 'Not allowed to store instructors.'], 403);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to add Instructor. Please try again later.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Instructor = Instructor::findOrFail($id);
            return new InstructorResource($Instructor);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateInstructorRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $instructor = Instructor::find($id);
                $validatedData = $request->validated();

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/instructors/';


                    if ($instructor->image && $instructor->image !== 'default.png' && file_exists(public_path($folderPath . $instructor->image))) {
                        unlink(public_path($folderPath . $instructor->image));
                    }

                    $image->move(public_path($folderPath), $filename);
                    $validatedData['image'] =  $filename;
                }

                $instructor->update($validatedData);
                return response()->json(new InstructorResource($instructor), 200);
            }
            return response()->json(['message' => 'Not allowed to update instructors.'], 403);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the instructor.'], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Instructor = Instructor::findOrFail($id);
                $Instructor->delete();
                return response()->json(['data' => 'Instructor deleted successfully'], 200);
            }
            return response()->json(['message' => 'Not allowed to update instructors.'], 403);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
