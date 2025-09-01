<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Resources\FavouriteResource;
use App\Models\Favourite;
use App\Traits\ApiResponse;
use Exception;
use App\Services\FavouriteService;

class FavouriteController extends Controller
{
    use ApiResponse;
    private $userId;
    private FavouriteService $favouriteService;

    public function __construct(FavouriteService $favouriteService)
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
        $this->favouriteService = $favouriteService;
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $favourites = $this->favouriteService->index($this->userId);
            return $this->success(FavouriteResource::collection($favourites));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function store(StoreFavouriteRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $favourite = $this->favouriteService->store($validatedData, $this->userId);

            if (!$favourite) {
                return $this->error('This product is already in your favourites.', 409);
            }

            return $this->success(new FavouriteResource($favourite), 201);
        } catch (Exception $e) {
            return $this->error('Failed to add favourite. Please try again later.', 500);
        }
    }

    public function show(string $id)
    {
        try {
            $favourite = $this->favouriteService->show($id, $this->userId);
            return $this->success(new FavouriteResource($favourite));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $favourite = $this->favouriteService->destroy($id, $this->userId);
            return $this->success('favourite deleted successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function clearFav()
    {
        try {
            $this->favouriteService->clearFav($this->userId);
            return $this->success('favourite cleared successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
