<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveMainSliderRequest;
use App\Http\Requests\StoreMainSliderRequest;
use App\Http\Requests\UpdateMainSliderRequest;
use App\Http\Resources\MainSliderResource;
use App\Services\MainSliderService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class MainSliderController extends Controller
{
    use ApiResponse;

    private $userId;
    private MainSliderService $mainSliderService;

    function __construct(MainSliderService $mainSliderService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->mainSliderService = $mainSliderService;
    }

    public function index()
    {
        try {
            $MainSliders = $this->mainSliderService->index();
            return $this->success(MainSliderResource::collection($MainSliders));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function all()
    {
        try {
            $addSideBarBanner = $this->mainSliderService->all();
            return $this->success(MainSliderResource::collection($addSideBarBanner));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function store(StoreMainSliderRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('image')) {
                $validatedData['image'] = $request->file('image');
            }
            $validatedData['admin_id'] = $this->userId;
            if (Gate::allows("is-admin")) {
                $MainSlider = $this->mainSliderService->store($validatedData);
                return $this->success(new MainSliderResource($MainSlider));
            } else {
                return $this->error('not allow to Store MainSlider.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $MainSlider = $this->mainSliderService->show($id);
            return $this->success(new MainSliderResource($MainSlider));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function active(ActiveMainSliderRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $MainSlider = $this->mainSliderService->active($validatedData, $id);
                return $this->success(new MainSliderResource($MainSlider));
            } else {
                return $this->error('not allow to active MainSlider.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateMainSliderRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('image')) {
                $validatedData['image'] = $request->file('image');
            }

            if (Gate::allows("is-admin")) {
                $MainSlider = $this->mainSliderService->update($validatedData, $id);
                return $this->success(new MainSliderResource($MainSlider));
            }
            return $this->error('not allow to update MainSlider.', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->mainSliderService->destroy($id);
                return $this->success(null, 'MainSlider deleted successfully');
            } else {
                return $this->error('not allow to delete MainSlider.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
