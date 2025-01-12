<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'post_comments';

    protected $fillable = [
        'user_id',
        'admin_id',
        'post_id',
        'status',
        'comment',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

}
