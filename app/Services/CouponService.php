<?php

namespace App\Services;

use App\Models\Coupon;

class CouponService
{

    public function index()
    {
        return Coupon::with('admin')->paginate(10);
    }

    public function store(array $data)
    {
        return Coupon::create($data);
    }

    public function show(string $id)
    {
        return Coupon::with('admin')->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $coupon = $this->show($id);
        $coupon->update($data);
        return $coupon;
    }

    public function destroy(string $id)
    {
        $coupon = $this->show($id);
        $coupon->update([
            'is_active' => false
        ]);

        $coupon->delete();
        return $coupon;
    }
    public function showByCode(string $code)
    {
        return Coupon::where('code', $code)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereColumn('uses_count', '<', 'max_uses')
            ->with('admin')
            ->first();

    }
}
