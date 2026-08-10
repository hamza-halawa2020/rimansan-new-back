<?php

namespace App\Services;

use App\Models\Shipment;

class ShipmentService
{
    public function index()
    {
        return Shipment::all();
    }

    public function store(array $data)
    {
        return Shipment::create($data);
    }

    public function show(string $id)
    {
        return Shipment::findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $shipment = $this->show($id);
        $shipment->update($data);
        return $shipment;
    }

    public function destroy(string $id)
    {
        $shipment = $this->show($id);
        $shipment->delete();
        return $shipment;
    }
}
