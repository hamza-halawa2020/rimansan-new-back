<?php

namespace App\Services;

use App\Mail\OrderCreatedMail;
use App\Models\Address;
use App\Models\City;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPoint;
use App\Models\User;
use App\Models\UserPoint;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderService
{
    public function index()
    {
        return Order::orderBy('created_at', 'desc')->paginate(10);
    }

    public function myOrders(int $userId)
    {
        return Order::where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);
    }

    public function trackOrder(string $orderNumber)
    {
        return Order::where('order_number', $orderNumber)->firstOrFail();
    }

    public function store($request, int $userId)
    {
        DB::beginTransaction();

        try {
            $validatedData = $this->prepareOrderData($request);
            $address = Address::create([
                'address' => $validatedData['address'] ?? null,
                'country_id' => $validatedData['country_id'] ?? null,
                'city_id' => $validatedData['city_id'],
                'user_id' => $userId,
            ]);

            $order = Order::create([
                'address_id' => $validatedData['address_id'] ?? $address->id,
                'user_id' => $userId,
                'coupon_id' => $validatedData['coupon_id'] ?? null,
                'shipment_id' => $validatedData['shipment_id'],
                'notes' => $validatedData['notes'] ?? null,
                'payment_method' => $validatedData['payment_method'],
                'coupon_discount' => $validatedData['coupon_discount'],
                'shipment_cost' => $validatedData['shipment_cost'],
                'total_price' => $validatedData['total_price'],
                'order_number' => $validatedData['order_number'],
            ]);

            $this->processOrderItems($order, $request->orderItems);
            $this->updateFinalTotal($order, $validatedData['coupon_discount'], $validatedData['shipment_cost']);

            DB::commit();
            $this->sendCreatedMail($order, User::find($userId)->email);
            Log::info('Order created successfully: ' . json_encode($order));

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function storeByClient($request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $this->prepareOrderData($request);

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

            $order = Order::create([
                'client_id' => $client->id,
                'address_id' => $address->id,
                'coupon_id' => $validatedData['coupon_id'] ?? null,
                'shipment_id' => $validatedData['shipment_id'],
                'notes' => $validatedData['notes'] ?? null,
                'payment_method' => $validatedData['payment_method'],
                'coupon_discount' => $validatedData['coupon_discount'] ?? 0,
                'shipment_cost' => $validatedData['shipment_cost'],
                'total_price' => $validatedData['total_price'],
                'order_number' => $validatedData['order_number'],
            ]);

            $this->processOrderItems($order, $request->orderItems);
            $this->updateFinalTotal($order, $validatedData['coupon_discount'], $validatedData['shipment_cost']);

            DB::commit();
            $this->sendCreatedMail($order, $client->email);
            Log::info('Order created successfully: ' . json_encode($order));

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function filterByStatus(string $status)
    {
        return Order::where('status', $status)->get();
    }

    public function show(string $id, int $userId)
    {
        return Order::where('user_id', $userId)->findOrFail($id);
    }

    public function cancel(string $id, int $userId, string $status)
    {
        $order = $this->show($id, $userId);
        $order->update(['status' => $status]);
        return $order;
    }

    public function changeStatus(string $id, string $status, int $adminId): array
    {
        $order = Order::with('orderItems')->find($id);

        if (!$order) {
            Log::error('Order not found:', ['id' => $id]);
            return [
                'status' => 404,
                'payload' => ['message' => 'Order not found.'],
            ];
        }

        $order->status = $status;
        $order->admin_id = $adminId;
        $order->save();

        if ($order->status == 'Delivered' && $order->user_id && !$order->client_id) {
            $existingPoints = UserPoint::where('order_id', $order->id)->exists();
            if ($existingPoints) {
                return [
                    'status' => 200,
                    'payload' => ['message' => 'Points already awarded for this order.'],
                ];
            }

            foreach ($order->orderItems as $item) {
                $productPoint = ProductPoint::where('product_id', $item->product->id)
                    ->whereNull('disabled_at')
                    ->latest()
                    ->first();

                if ($productPoint) {
                    $totalPoints = $productPoint->points * $item->quantity;
                    UserPoint::create([
                        'user_id' => $adminId,
                        'product_point_id' => $productPoint->id,
                        'order_id' => $order->id,
                        'points' => $totalPoints,
                    ]);
                }
            }
        }

        Log::info('Order status updated successfully:', ['order' => $order]);

        return [
            'status' => 200,
            'payload' => ['message' => 'Order status updated successfully.', 'order' => $order],
        ];
    }

    private function updateFinalTotal(Order $order, $couponDiscount, $shipmentCost): void
    {
        $finalTotal = $this->calculateFinalTotal($order, $couponDiscount, $shipmentCost);
        $order->update(['total_price' => $finalTotal]);

        if ($order->payment_method != 'cash_on_delivery') {
            $order->update(['status' => 'Awaiting Payment']);
        }
    }

    private function calculateFinalTotal(Order $order, $couponDiscount, $shipmentCost)
    {
        $totalWithoutCoupon = $order->orderItems->sum('total');
        $discountAmount = ($couponDiscount / 100) * $totalWithoutCoupon;

        return $totalWithoutCoupon - $discountAmount + $shipmentCost;
    }

    private function processOrderItems(Order $order, array $orderItems): void
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

    private function prepareOrderData($request): array
    {
        $validatedData = $request->validated();

        if (isset($validatedData['coupon_id'])) {
            $coupon = Coupon::findOrFail($validatedData['coupon_id']);
            $currentDate = now();

            if ($coupon->is_active != 1 || $coupon->end_date < $currentDate) {
                throw new Exception("The selected coupon is expired or inactive.");
            }

            if ($coupon->uses_count >= $coupon->max_uses) {
                throw new Exception("The selected coupon has reached its maximum usage limit.");
            }

            $validatedData['coupon_discount'] = $coupon->discount;
            $coupon->uses_count++;
            $coupon->save();
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

    private function sendCreatedMail(Order $order, string $userEmail): void
    {
        $adminEmails = User::where('type', 'admin')->pluck('email')->toArray();
        $allEmails = array_merge($adminEmails, [$userEmail]);
        Log::info('All emails: ' . json_encode($allEmails));

        foreach ($allEmails as $email) {
            Mail::to($email)->queue(new OrderCreatedMail($order));
        }
    }
}
