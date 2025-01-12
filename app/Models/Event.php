<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';
    protected $fillable = [
        'title',
        'content',
        'image',
        'admin_id',
        'category_id',
        'tag_id',
    ];


    public function admin()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function eventImages()
    {
        return $this->hasMany(EventImage::class, 'event_id');
    }


}
