<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{

    public function index()
    {
        return Client::with('addresses')->get();
    }

    public function store(array $data)
    {
        return Client::create($data);
    }

    public function show(string $id)
    {
        return Client::with('addresses')->findOrFail($id);
    }

    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return $client;
    }
}
