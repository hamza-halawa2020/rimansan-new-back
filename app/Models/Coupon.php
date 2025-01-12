<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'coupons';
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount',
        'max_uses',
        'uses_count',
        'start_date',
        'end_date',
        'is_active',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
