<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string'; // Set the key type to string
    public $incrementing = false; // Disable auto-incrementing IDs

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid(); // Generate UUID
            }
        });
    }

    protected $table = 'payments';
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'status',
        'notes',
        'paymob_order_id',
        'transaction_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
