<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Services\AddressService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    use ApiResponse;
    private $userId;
    private AddressService $addressService;

    function __construct(AddressService $addressService)
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->addressService = $addressService;
    }
    public function adminIndex()
    {
        try {
            if (Gate::allows("is-admin")) {
                $addresses = $this->addressService->adminIndex();
                return $this->success(AddressResource::collection($addresses));
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function index()
    {
        try {
            $addresses = $this->addressService->index($this->userId);
            return $this->success(AddressResource::collection($addresses));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreAddressRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = $this->userId;
            $address = $this->addressService->store($validatedData);
            return $this->success(new AddressResource($address), 'Address created successfully', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function show(string $id)
    {
        try {
            $address = $this->addressService->show($id, $this->userId);
            return $this->success(new AddressResource($address));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function update(UpdateAddressRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $address = $this->addressService->update($id, $this->userId, $validatedData);
            return $this->success(new AddressResource($address), 'Address updated successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->addressService->destroy($id, $this->userId);
            return $this->success(null, 'Address deleted successfully', 204);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
