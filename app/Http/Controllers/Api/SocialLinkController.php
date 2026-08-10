<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialLinkRequest;
use App\Http\Requests\UpdateSocialLinkRequest;
use App\Http\Resources\SocialLinkResource;
use App\Services\SocialLinkService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class SocialLinkController extends Controller
{
    use ApiResponse;

    private SocialLinkService $socialLinkService;

    function __construct(SocialLinkService $socialLinkService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->socialLinkService = $socialLinkService;
    }

    public function index()
    {
        try {
            $SocialLinks = $this->socialLinkService->index();
            return $this->success(SocialLinkResource::collection($SocialLinks));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreSocialLinkRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('icon')) {
                $validatedData['icon'] = $request->file('icon');
            }
            $adminId = auth()->id();
            $validatedData['admin_id'] = $adminId;
            if (Gate::allows("is-admin")) {
                $SocialLink = $this->socialLinkService->store($validatedData);
                return $this->success(new SocialLinkResource($SocialLink));
            } else {
                return $this->error('not allow to Store SocialLink.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $SocialLink = $this->socialLinkService->show($id);
            return $this->success(new SocialLinkResource($SocialLink));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateSocialLinkRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('icon')) {
                $validatedData['icon'] = $request->file('icon');
            }

            if (!Gate::allows("is-admin")) {
                return $this->error('Not allowed to update SocialLink.', 403);
            }

            $socialLink = $this->socialLinkService->update($validatedData, $id);
            return $this->success(new SocialLinkResource($socialLink));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->socialLinkService->destroy($id);
                return $this->success(null, 'SocialLink deleted successfully');
            } else {
                return $this->error('not allow to delete SocialLink.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
