<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasFactory;

    protected $table = 'verification_codes';
    protected $fillable = [
        'user_id',
        'verification_code',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);

    }

}
