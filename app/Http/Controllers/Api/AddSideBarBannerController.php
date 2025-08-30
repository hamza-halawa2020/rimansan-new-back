<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddSideBarBannerRequest;
use App\Http\Requests\UpdateAddSideBarBannerRequest;
use App\Http\Resources\AddSideBarBannerResource;
use App\Services\AddSideBarBannerService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class AddSideBarBannerController extends Controller
{
    use ApiResponse;

    private AddSideBarBannerService $addSideBarBannerService;

    function __construct(AddSideBarBannerService $addSideBarBannerService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->addSideBarBannerService = $addSideBarBannerService;
    }

    public function index()
    {
        try {
            $addSideBarBanner = $this->addSideBarBannerService->index();
            return $this->success(AddSideBarBannerResource::collection($addSideBarBanner));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function all()
    {
        try {
            $addSideBarBanner = $this->addSideBarBannerService->all();
            return $this->success(AddSideBarBannerResource::collection($addSideBarBanner));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function store(StoreAddSideBarBannerRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $adminId = auth()->id();
            $validatedData['admin_id'] = $adminId;
            if (Gate::allows("is-admin")) {
                $addSideBarBanner = $this->addSideBarBannerService->store($validatedData);
                return $this->success(new AddSideBarBannerResource($addSideBarBanner), 'Address created successfully', 201);
            } else {
                return $this->error('not allow to Store AddSideBarBanner.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $addSideBarBanner = $this->addSideBarBannerService->show($id);
            return $this->success(new AddSideBarBannerResource($addSideBarBanner));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateAddSideBarBannerRequest $request, string $id)
    {

        try {
            $validatedData = $request->validated();

            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to update AddSideBarBanner.', 403);
            }

            $addSideBarBanner = $this->addSideBarBannerService->update($validatedData, $id);
            return $this->success(new AddSideBarBannerResource($addSideBarBanner), 'AddSideBarBanner updated successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $this->addSideBarBannerService->destroy($id);
                return $this->success(null, 'addSideBarBanner deleted successfully', 204);
            } else {
                return $this->error('not allow to delete AddSideBarBanner.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
