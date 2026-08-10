<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    use ApiResponse;

    private TagService $tagService;

    function __construct(TagService $tagService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->tagService = $tagService;
    }

    public function index()
    {
        try {
            $tags = $this->tagService->index();
            return $this->success(TagResource::collection($tags));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreTagRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $tag = $this->tagService->store($validatedData);
                return $this->success(new TagResource($tag));
            } else {
                return $this->error('not allow to Store tag.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $tag = $this->tagService->show($id);
            return $this->success(new TagResource($tag));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTagRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $tag = $this->tagService->update($validatedData, $id);
                return $this->success(new TagResource($tag));
            } else {
                return $this->error('not allow to update tag.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->tagService->destroy($id);
                return $this->success(null, 'tag deleted successfully');
            } else {
                return $this->error('not allow to delete tag.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
