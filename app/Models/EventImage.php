<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    use HasFactory;
    protected $table = 'event_images';

    protected $fillable = [
        'image',
        'event_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
