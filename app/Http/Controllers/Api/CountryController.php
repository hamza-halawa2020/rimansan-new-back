<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Exception;
use Illuminate\Support\Facades\Gate;

class CountryController extends Controller
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
            $countries = Country::all();
            return CountryResource::collection($countries);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $Country = Country::create($validatedData);
                return response()->json(['data' => new CountryResource($Country)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store Country.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        try {
            $Country = Country::findOrFail($id);
            return new CountryResource($Country);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            if (Gate::allows("is-admin")) {
                $Country = Country::findOrFail($id);
                $Country->update($validatedData);
                return response()->json(['data' => new CountryResource($Country)], 200);
            } else {
                return response()->json(['message' => 'not allow to update Country.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $user = Country::findOrFail($id);
                $user->delete();
                return response()->json(['data' => 'Country deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete Country.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
