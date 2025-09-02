<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstructorRequest;
use App\Http\Requests\UpdateInstructorRequest;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use App\Services\InstructorService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class InstructorController extends Controller
{

    use ApiResponse;
    private $userId;
    private InstructorService $instructorService;

    function __construct(InstructorService $instructorService)
    {
        $this->instructorService = $instructorService;
        $this->middleware("auth:sanctum")->except('index', 'show', 'randomInstructors');
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $instructors = $this->instructorService->index();
            return $this->success(InstructorResource::collection($instructors));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function randomInstructors()
    {
        try {
            $instructors = $this->instructorService->randomInstructors();
            return $this->success(InstructorResource::collection($instructors));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreInstructorRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $validatedData['admin_id'] = $this->userId;
                $instructor = $this->instructorService->store($validatedData);
                return $this->success(new InstructorResource($instructor), 201);
            }
            return $this->error('Not allowed to store instructors.', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $instructor = $this->instructorService->show($id);
            return $this->success(new InstructorResource($instructor));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateInstructorRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $instructor = $this->instructorService->update($validatedData, $id);
                return $this->success(new InstructorResource($instructor), 200);
            }
            return $this->error('Not allowed to update instructors.', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->instructorService->destroy($id);
                return $this->success('Instructor deleted successfully', 200);
            }
            return $this->error('Not allowed to delete instructors.', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
