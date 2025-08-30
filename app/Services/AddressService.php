<?php

namespace App\Services;

use App\Models\Address;

class AddressService
{

    public function adminIndex()
    {
        return Address::with('country', 'city', 'user', 'client')->paginate(10);
    }

    public function index(int $userId)
    {
        return Address::where('user_id', $userId)->with('country', 'city', 'user', 'client')->paginate(10);
    }

    public function store(array $data)
    {
        return Address::create($data);
    }

    public function show(string $id, int $userId)
    {
        return Address::where('user_id', $userId)->where('id', $id)->with('country', 'city', 'user', 'client')->firstOrFail();
    }

    public function update(string $id, int $userId, array $data)
    {
        $address = $this->show($id, $userId);
        $address->update($data);
        return $address;
    }

    public function destroy(string $id, int $userId)
    {
        $address = $this->show($id, $userId);
        $address->delete();
        return $address;
    }
}
