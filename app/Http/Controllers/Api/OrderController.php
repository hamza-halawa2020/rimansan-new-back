<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\Gate;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PendingOrdersExport;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use ApiResponse;

    private $userId;
    private OrderService $orderService;

    function __construct(OrderService $orderService)
    {
        $this->middleware("auth:sanctum")->except(['storeByClient','trackOrder']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
        $this->orderService = $orderService;
    }



    public function exportPendingOrders()
    {
        try {
            $fileName = 'pending-orders.xlsx';
            return Excel::download(new PendingOrdersExport, $fileName);
        } catch (Exception $e) {
            Log::error('Error exporting pending orders: ' . $e->getMessage());
            return $this->error('Internal Server Error', 500);
        }
    }



    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $orders = $this->orderService->index();
                return $this->success(OrderResource::collection($orders));
            } else {
                return $this->error('not allow to show orders.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function myOrders()
    {
        try {
            $orders = $this->orderService->myOrders($this->userId);
            return $this->success(OrderResource::collection($orders));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function trackOrder($orderNumber)
    {
        try {
            $order = $this->orderService->trackOrder($orderNumber);
            return $this->success(new OrderResource($order));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->store($request, $this->userId);
            return $this->success(new OrderResource($order));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function storeByClient(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->storeByClient($request);
            return $this->success(new OrderResource($order));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }



    public function filterByStatus($status)
    {
        try {
            if (Gate::allows("is-admin")) {
                $order = $this->orderService->filterByStatus($status);
                return $this->success(OrderResource::collection($order));
            } else {
                return $this->error('not allow to show Order.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }



    public function show($id)
    {
        try {
            $order = $this->orderService->show($id, $this->userId);
            return $this->success(new OrderResource($order));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:Canceled',
            ]);
            $order = $this->orderService->cancel($id, $this->userId, $validated['status']);
            return $this->success(new OrderResource($order));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        Log::info('Received request to change status for order:', ['id' => $id, 'payload' => $request->all()]);

        if (!Gate::allows('is-admin')) {
            return $this->error('You are not authorized to change the order status.', 403);
        }

        $validated = $request->validate([
            'status' => 'required',
        ]);
        $result = $this->orderService->changeStatus($id, $validated['status'], $this->userId);
        if ($result['status'] >= 400) {
            return $this->error($result['payload']['message'] ?? 'error', $result['status']);
        }

        return $this->success($result['payload'], $result['payload']['message'] ?? 'success', $result['status']);
    }
}
