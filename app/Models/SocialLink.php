<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;
    protected $table = 'social_links';

    protected $fillable = [
        'name',
        'url',
        'icon',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class);
    }

}
