<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $fillable = [
        'name',
        'image'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}

