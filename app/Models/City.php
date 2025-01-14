<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $table = 'cities';
    protected $fillable = [
        'name',
        'country_id',
    ];
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
