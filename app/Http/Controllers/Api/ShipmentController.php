<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Http\Resources\ShipmentResource;
use App\Services\ShipmentService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    use ApiResponse;

    private $userId;
    private ShipmentService $shipmentService;

    function __construct(ShipmentService $shipmentService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->shipmentService = $shipmentService;
    }

    public function index()
    {
        try {
            $cities = $this->shipmentService->index();
            return $this->success(ShipmentResource::collection($cities));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreShipmentRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $validatedData['user_id'] = $this->userId;
                $Shipment = $this->shipmentService->store($validatedData);
                return $this->success(new ShipmentResource($Shipment));
            } else {
                return $this->error('not allow to Store shipment.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function show(string $id)
    {
        try {
            $Shipment = $this->shipmentService->show($id);
            return $this->success(new ShipmentResource($Shipment));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function update(UpdateShipmentRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $Shipment = $this->shipmentService->update($validatedData, $id);
                return $this->success(new ShipmentResource($Shipment));
            } else {
                return $this->error('not allow to Store shipment.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->shipmentService->destroy($id);
                return $this->success(null, 'Shipment deleted successfully');
            } else {
                return $this->error('not allow to Store shipment.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
