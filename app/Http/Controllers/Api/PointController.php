<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPointRequest;
use App\Http\Resources\ProductPointResource;
use App\Services\PointService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class PointController extends Controller
{
    use ApiResponse;

    private $userId;
    private PointService $pointService;

    function __construct(PointService $pointService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->pointService = $pointService;
    }

    public function index()
    {
        try {
            $points = $this->pointService->index();
            return $this->success(ProductPointResource::collection($points));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreProductPointRequest $request)
    {
        try {
            $validatedData = $request->validated();

            if (Gate::allows("is-admin")) {
                $point = $this->pointService->store($validatedData, $this->userId);
                return $this->success(new ProductPointResource($point));
            } else {
                return $this->error('Not allowed to store point.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function show(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $point = $this->pointService->show($id);
                return $this->success(new ProductPointResource($point));
            } else {
                return $this->error('not allow to show Point.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
