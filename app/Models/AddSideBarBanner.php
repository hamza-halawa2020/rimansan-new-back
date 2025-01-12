<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddSideBarBanner extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'add_side_bar_banners';

    protected $fillable = [
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
