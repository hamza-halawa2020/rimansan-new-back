<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Services\CityService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class CityController extends Controller
{
    use ApiResponse;
    private $userId;
    private CityService $cityService;

    function __construct(CityService $cityService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

        $this->cityService = $cityService;
    }

    public function index()
    {
        try {
            $cities = $this->cityService->index();
            return $this->success(CityResource::collection($cities));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreCityRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $exists = City::where('country_id', $validatedData['country_id'])->where('name', $validatedData['name'])->exists();
                if ($exists) {
                    return $this->error('This City is already exists.', 409);
                }
                $city = $this->cityService->store($validatedData);
                return $this->success(new CityResource($city), 201);
            } else {
                return $this->error('not allow to Store City.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $city = $this->cityService->show($id);
            return $this->success(new CityResource($city));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function update(UpdateCityRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $exists = City::where('country_id', $validatedData['country_id'])->where('name', $validatedData['name'])->where('id', '!=', $id)->exists();
                if ($exists) {
                    return $this->error('This City already exists in the specified country.', 409);
                }

                $city = $this->cityService->update($validatedData, $id);
                return $this->success(new CityResource($city));
            } else {
                return $this->error('not allow to update City.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $city = $this->cityService->destroy($id);
                return $this->success('City deleted successfully', 204);
            } else {
                return $this->error('not allow to delete City.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getCitiesByCountry($countryId)
    {
        try {
            $cities = $this->cityService->getCitiesByCountry($countryId);
            return $this->success(CityResource::collection($cities));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
