<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'admin_id',
        'client_id',
        'address_id',
        'coupon_id',
        'shipment_id',
        'total_price',
        'order_number',
        'notes',
        'payment_method',
        'status',
        'shipment_cost',
        'coupon_discount',
        'paymob_order_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function client()
    {
        return $this->belongsTo(Client::class, );
    }
    public function address()
    {
        return $this->belongsTo(Address::class, );
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, );
    }
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, );
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }



}

