<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use Exception;
use Illuminate\Support\Facades\Gate;

class CityController extends Controller
{
    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $cities = City::all();
            return CityResource::collection($cities);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreCityRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {


                $exists = City::where('country_id', $validatedData['country_id'])->where('name', $validatedData['name'])->exists();
                if ($exists) {
                    return response()->json(['message' => 'This City is already exists.'], 409);
                }

                $City = City::create($validatedData);
                return response()->json(['data' => new CityResource($City)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store City.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $City = City::findOrFail($id);
            return new CityResource($City);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function update(UpdateCityRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $exists = City::where('country_id', $validatedData['country_id'])
                    ->where('name', $validatedData['name'])
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    return response()->json(['message' => 'This City already exists in the specified country.'], 409);
                }
                $City = City::findOrFail($id);
                $City->update($validatedData);
                return response()->json(['data' => new CityResource($City)], 200);
            } else {
                return response()->json(['message' => 'not allow to update City.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $user = City::findOrFail($id);
                $user->delete();
                return response()->json(['data' => 'City deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete City.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function getCitiesByCountry($countryId)
    {
        try {
            $cities = City::where('country_id', $countryId)->get();
            return CityResource::collection($cities);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
