<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'address',
        'country_id',
        'city_id',
        'user_id',
        'client_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}