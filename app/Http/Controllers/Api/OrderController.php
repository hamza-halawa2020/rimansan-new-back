<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\City;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Str;
use DB;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\Gate;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PendingOrdersExport;
class OrderController extends Controller
{
    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['storeByClient']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

    }



    public function exportPendingOrders()
    {
        $fileName = 'pending-orders.xlsx';
        return Excel::download(new PendingOrdersExport, $fileName);
    }



    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $orders = Order::paginate(10);
                return OrderResource::collection($orders);
            } else {
                return response()->json(['message' => 'not allow to show orders.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function myOrders()
    {
        try {
            $orders = Order::where('user_id', $this->userId, )->paginate(10);
            return OrderResource::collection($orders);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function trackOrder($orderNumber)
    {
        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();
            return new OrderResource($order);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $validatedData['user_id'] = $this->userId;
            $validatedData = $this->prepareOrderData($request);
            // $order = Order::create($validatedData);
            $address = Address::create([
                'address' => $validatedData['address'],
                'country_id' => $validatedData['country_id'],
                'city_id' => $validatedData['city_id'],
                'user_id' => $this->userId,
            ]);
            $order = Order::create([
                'address_id' => $validatedData['address_id'] ?? $address->id,
                'user_id' => $this->userId,
                'coupon_id' => $validatedData['coupon_id'],
                'shipment_id' => $validatedData['shipment_id'],
                'notes' => $validatedData['notes'],
                'payment_method' => $validatedData['payment_method'],
                'coupon_discount' => $validatedData['coupon_discount'],
                'shipment_cost' => $validatedData['shipment_cost'],
                'total_price' => $validatedData['total_price'],
                'order_number' => $validatedData['order_number'],
            ]);
            $this->processOrderItems($order, $request->orderItems);
            $finalTotal = $this->calculateFinalTotal($order, $validatedData['coupon_discount'], $validatedData['shipment_cost']);
            $order->update(['total_price' => $finalTotal]);
            if ($order->payment_method != 'cash_on_delivery') {
                // $this->processPayment($order, $request);
                $order->update(['status' => 'Awaiting Payment']);
            }
            DB::commit();
            return response()->json(['data' => new OrderResource($order)], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function storeByClient(StoreOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $client = Client::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
            ]);
            $address = Address::create([
                'address' => $validatedData['address'],
                'country_id' => $validatedData['country_id'],
                'city_id' => $validatedData['city_id'],
            ]);
            $validatedData = $this->prepareOrderData($request);
            $order = Order::create([
                'client_id' => $client->id,
                'address_id' => $address->id,
                'coupon_id' => $validatedData['coupon_id'],
                'shipment_id' => $validatedData['shipment_id'],
                'notes' => $validatedData['notes'],
                'payment_method' => $validatedData['payment_method'],
                'coupon_discount' => $validatedData['coupon_discount'],
                'shipment_cost' => $validatedData['shipment_cost'],
                'total_price' => $validatedData['total_price'],
                'order_number' => $validatedData['order_number'],
            ]);



            $this->processOrderItems($order, $request->orderItems);

            $finalTotal = $this->calculateFinalTotal($order, $validatedData['coupon_discount'], $validatedData['shipment_cost']);
            $order->update(['total_price' => $finalTotal]);
            if ($order->payment_method != 'cash_on_delivery') {
                // $this->processPayment($order, $request);
                $order->update(['status' => 'Awaiting Payment']);

            }
            DB::commit();
            return response()->json(['data' => new OrderResource($order)], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function filterByStatus($status)
    {
        try {
            if (Gate::allows("is-admin")) {
                $order = Order::where('status', $status, )->get();
                return new OrderResource($order);
            } else {
                return response()->json(['message' => 'not allow to show Order.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }



    public function show($id)
    {
        try {
            $order = Order::where('user_id', $this->userId, )->findOrFail($id);
            return new OrderResource($order);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:Canceled',
            ]);
            $order = Order::where('user_id', $this->userId, )->findOrFail($id);
            $order->update(['status' => $validated['status']]);
            return new OrderResource($order);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function changeStatus(Request $request, string $id)
    {
        try {
            if (!Gate::allows('is-admin')) {
                return response()->json(['message' => 'You are not authorized to change the order status.'], 403);
            }
            $validated = $request->validate([
                'status' => 'required|string|in:Pending,Returned,Shipped,Delivered',
            ]);
            $order = Order::where('user_id', $this->userId)->findOrFail($id);
            $order->update(['status' => $validated['status']]);
            return new OrderResource($order);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }




    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Order = Order::findOrFail($id);
                $Order->update(['notes' => "order delete by admin id : {$this->userId}"]);
                $Order->delete();
                return response()->json(['message' => 'Order deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to show Order.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
    private function calculateFinalTotal(Order $order, $couponDiscount, $shipmentCost)
    {
        $totalWithoutCoupon = $order->orderItems->sum('total');
        $discountAmount = ($couponDiscount / 100) * $totalWithoutCoupon;
        return $totalWithoutCoupon - $discountAmount + $shipmentCost;
    }
    private function processOrderItems(Order $order, array $orderItems)
    {
        foreach ($orderItems as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $total = $product->priceAfterDiscount * $itemData['quantity'];
            $order->orderItems()->create([
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'order_id' => $order->id,
                'total' => $total,
            ]);
        }
    }

    private function processPayment(Order $order, $request)
    {
        try {
            $validatedData = $request->validated();
            $paymentAmount = $order->total_price;

            $order->payments()->create([
                'order_id' => $order->id,
                'payment_method' => $validatedData['payment_method'],
                'amount' => $paymentAmount,
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to process payment: " . $e->getMessage());
        }
    }
    private function prepareOrderData($request)
    {
        $validatedData = $request->validated();
        if (isset($validatedData['coupon_id'])) {
            $coupon = Coupon::findOrFail($validatedData['coupon_id']);
            $validatedData['coupon_discount'] = $coupon->discount;
        } else {
            $validatedData['coupon_discount'] = 0;
        }
        $city = City::findOrFail($validatedData['city_id']);
        $shipment = $city->shipments()->firstOrFail();
        $validatedData['shipment_id'] = $shipment->id;
        $validatedData['shipment_cost'] = $shipment->cost;
        $validatedData['total_price'] = 0;
        $validatedData['order_number'] = 'ORD-' . Str::uuid();

        return $validatedData;
    }

}
