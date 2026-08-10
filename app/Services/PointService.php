<?php

namespace App\Services;

use App\Models\ProductPoint;

class PointService
{
    public function index()
    {
        return ProductPoint::with('product', 'createdBy')->latest()->get();
    }

    public function store(array $data, int $userId)
    {
        $data['created_by'] = $userId;
        ProductPoint::where('product_id', $data['product_id'])
            ->whereNull('disabled_at')
            ->update(['disabled_at' => now()]);

        return ProductPoint::create($data);
    }

    public function show(string $id)
    {
        return ProductPoint::with('product', 'createdBy')->findOrFail($id);
    }
}
