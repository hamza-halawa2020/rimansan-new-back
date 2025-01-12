<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'courses';
    protected $fillable = [
        'admin_id',
        'category_id',
        'tag_id',
        'instructor_id',
        'title',
        'description',
        'video_url',
        'image',
        'price',
        'certifications',

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
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
    public function courseReviews()
    {
        return $this->hasMany(CourseReview::class);
    }
}
