<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendingOrdersExport implements FromCollection, WithHeadings
{
    // protected $maxOrderItems = 5; // Maximum number of order items to display per order

    /**
     * @return \Illuminate\Support\Collection
     */

    protected $maxOrderItems;

    public function __construct()
    {
        $this->maxOrderItems = Order::where('status', 'Pending')
            ->withCount('orderItems')
            ->get()
            ->max('order_items_count');
    }

    public function collection()
    {
        $orders = Order::where('status', 'Pending')
            ->with(['user', 'client', 'address.country', 'address.city', 'orderItems.product'])
            ->get();

        return $orders->map(function ($order) {
            $data = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'notes' => $order->notes ? $order->notes : 'N/A',
                'payment_method' => $order->payment_method,
                'user_name' => $order->user ? $order->user->name : ($order->client ? $order->client->name : 'N/A'),
                'user_email' => $order->user ? $order->user->email : ($order->client ? $order->client->email : 'N/A'),
                'user_phone' => $order->user ? $order->user->phone : ($order->client ? $order->client->phone : 'N/A'),
                'country' => $order->address && $order->address->country ? $order->address->country->name : 'N/A',
                'city' => $order->address && $order->address->city ? $order->address->city->name : 'N/A',
                'address' => $order->address ? $order->address->address : 'N/A',
                'coupon_discount' => $order->coupon_discount,
                'shipment_cost' => $order->shipment_cost,
                'total_price' => $order->total_price,
            ];

            foreach ($order->orderItems->take($this->maxOrderItems) as $index => $item) {
                $data["product_title_" . ($index + 1)] = $item->product ? $item->product->title : 'N/A';
                $data["quantity_" . ($index + 1)] = $item->quantity;
                $data["total_" . ($index + 1)] = $item->total;
            }

            for ($i = $order->orderItems->count(); $i < $this->maxOrderItems; $i++) {
                $data["product_title_" . ($i + 1)] = 'N/A';
                $data["quantity_" . ($i + 1)] = 'N/A';
                $data["total_" . ($i + 1)] = 'N/A';
            }

            return $data;
        });
    }

    public function headings(): array
    {
        $headings = [
            'Order ID',
            'Order Number',
            'Created At',
            'Notes',
            'Payment Method',
            'User/Client Name',
            'User/Client Email',
            'User/Client Phone',
            'Country',
            'City',
            'Address',
            'Coupon Discount',
            'Shipment Cost',
            'Total Price',
        ];

        for ($i = 1; $i <= $this->maxOrderItems; $i++) {
            $headings[] = "Product Title $i";
            $headings[] = "Quantity $i";
            $headings[] = "Total $i";
        }

        return $headings;
    }
}
