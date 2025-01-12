<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'products';

    protected $fillable = [
        'title',
        'description',
        'stock',
        'priceBeforeDiscount',
        'discount',
        'priceAfterDiscount',
        'category_id',
        'admin_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function admin()
    {
        return $this->belongsTo(User::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }
    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
