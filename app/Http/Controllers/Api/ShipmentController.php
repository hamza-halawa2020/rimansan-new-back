<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Http\Resources\ShipmentResource;
use App\Models\Shipment;
use Exception;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
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
            $cities = Shipment::paginate(10);
            return ShipmentResource::collection($cities);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreShipmentRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $validatedData['user_id'] = $this->userId;
                $Shipment = Shipment::create($validatedData);
                return response()->json(['data' => new ShipmentResource($Shipment)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store shipment.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    public function show(string $id)
    {
        try {
            $Shipment = Shipment::findOrFail($id);
            return new ShipmentResource($Shipment);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function update(UpdateShipmentRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $Shipment = Shipment::findOrFail($id);
                $Shipment->update($validatedData);
                return response()->json(['data' => new ShipmentResource($Shipment)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store shipment.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Shipment = Shipment::findOrFail($id);
                $Shipment->delete();
                return response()->json(['data' => 'Shipment deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to Store shipment.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
