<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    use HasFactory;

    protected $table = 'reset_passwords';
    protected $fillable = [
        'user_id',
        'token',
        'expires_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);

    }
}
