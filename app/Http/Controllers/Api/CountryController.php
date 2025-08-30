<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Services\CountryService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class CountryController extends Controller
{
    use ApiResponse;

    private $userId;
    private CountryService $countryService;

    function __construct(CountryService $countryService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

        $this->countryService = $countryService;
    }

    public function index()
    {
        try {
            $countries = $this->countryService->index();
            return $this->success(CountryResource::collection($countries));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreCountryRequest $request)
    {
        try {
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to Store Country.', 403);
            }

            $validatedData = $request->validated();
            $country = $this->countryService->store($validatedData);
            return $this->success(new CountryResource($country), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $country = $this->countryService->show($id);
            return $this->success(new CountryResource($country));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCountryRequest $request, string $id)
    {
        try {
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to update Country.', 403);
            }

            $validatedData = $request->validated();
            $country = $this->countryService->update($validatedData, $id);
            return $this->success(new CountryResource($country));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to delete Country.', 403);
            }

            $this->countryService->destroy($id);
            return $this->success('Country deleted successfully', 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
