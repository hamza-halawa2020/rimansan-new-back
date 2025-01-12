<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $table = 'countries';
    protected $fillable = [
        'name',
    ];
    public function cities()
    {
        return $this->hasMany(City::class);
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

