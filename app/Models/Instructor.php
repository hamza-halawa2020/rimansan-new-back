<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instructor extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'instructors';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'image',
        'description',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class);
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
