<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MainSlider extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'main_silders';

    protected $fillable = [
        'title',
        'description',
        'image',
        'link',
        'status',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class);
    }
}
