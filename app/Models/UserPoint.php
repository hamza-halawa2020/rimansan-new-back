<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    protected $table = 'user_points';

    protected $fillable = [
        'user_id',
        'product_point_id',
        'order_id',
        'points'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function productPoint()
    {
        return $this->belongsTo(ProductPoint::class, 'product_point_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
