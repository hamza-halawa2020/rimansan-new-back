<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Exception;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

    }
    public function adminIndex()
    {
        try {
            if (Gate::allows("is-admin")) {
                $Address = Address::paginate(10);
                return AddressResource::collection($Address);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    public function index()
    {
        try {
            $Address = Address::where('user_id', $this->userId)->paginate(10);
            return AddressResource::collection($Address);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreAddressRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = $this->userId;
            $Address = Address::create($validatedData);
            return response()->json(['data' => new AddressResource($Address)], 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    public function show(string $id)
    {
        try {
            $Address = Address::where('user_id', $this->userId)->findOrFail($id);
            return new AddressResource($Address);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function update(UpdateAddressRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $Address = Address::where('user_id', $this->userId)->findOrFail($id);
            $Address->update($validatedData);
            return response()->json(['data' => new AddressResource($Address)], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $Address = Address::where('user_id', $this->userId)->findOrFail($id);
            $Address->delete();
            return response()->json(['data' => 'Address deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
