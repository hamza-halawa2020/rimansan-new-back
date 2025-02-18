<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Exception;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    function __construct()
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
    }
    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $coupons = Coupon::paginate(10);
                return CouponResource::collection($coupons);
            } else {
                return response()->json(['message' => 'not allow to show coupons.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreCouponRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();
                $adminId = auth()->id();
                $validatedData['admin_id'] = $adminId;
                $coupon = Coupon::create($validatedData);
                return response()->json(['data' => new CouponResource($coupon)], 200);
            } else {
                return response()->json(['message' => 'not allow to show coupons.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }



    public function showCoupon(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
            ]);

            $coupon = Coupon::where('code', $request->code)
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereColumn('uses_count', '<', 'max_uses')
                ->first();

            if (!$coupon) {
                return response()->json(['message' => 'Invalid or expired coupon.'], 400);
            }

            return response()->json([
                'message' => 'Coupon found!',
                'coupon' => new CouponResource($coupon),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching the coupon.'], 500);
        }
    }



    public function show(string $id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            return new CouponResource($coupon);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateCouponRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $coupon = Coupon::find($id);
                if (!$coupon) {
                    return response()->json(['message' => 'Coupon not found.'], 404);
                }
                $validatedData = $request->validated();
                $coupon->update($validatedData);
                return response()->json(new CouponResource($coupon), 200);
            }
            return response()->json(['message' => 'Not allowed to update coupons.'], 403);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the coupon.'], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $coupon = Coupon::findOrFail($id);
                $coupon->update([
                    'is_active' => false
                ]);

                $coupon->delete();
                return response()->json(['data' => 'coupon deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete coupon.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
