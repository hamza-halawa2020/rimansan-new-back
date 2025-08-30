<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Services\CouponService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Exception;

class CouponController extends Controller
{

    use ApiResponse;
    private CouponService $couponService;
    function __construct(CouponService $couponService)
    {
        $this->middleware("auth:sanctum")->except('showCoupon');
        $this->middleware("limitReq");

        $this->couponService = $couponService;
    }
    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $coupons =  $this->couponService->index();
                return $this->success(CouponResource::collection($coupons));
            } else {
                return $this->error('not allow to show coupons.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreCouponRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {

                $validatedData = $request->validated();
                $adminId = auth()->id();
                $validatedData['admin_id'] = $adminId;
                $coupon =  $this->couponService->store($validatedData);
                return $this->success(new CouponResource($coupon), 200);
            } else {
                return $this->error('not allow to show coupons.', 403);
            }
        } catch (Exception $e) {
            return  $this->error($e->getMessage(), 500);
        }
    }



    public function showCoupon(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
            ]);
            $coupon = $this->couponService->showByCode($request->code);
            if (!$coupon) {
                return $this->error('Invalid or expired coupon.', 400);
            }
            return $this->success(new CouponResource($coupon));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function show(string $id)
    {
        try {
            $coupon =  $this->couponService->show($id);
            return $this->success(new CouponResource($coupon));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCouponRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $coupon =  $this->couponService->update($validatedData, $id);
                return $this->success(new CouponResource($coupon));
            } else {
                return $this->error('not allow to show coupons.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }



    public function destroy(string $id)
    {
        try {
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to delete coupon.', 403);
            }
            $this->couponService->destroy($id);
            return $this->success('Coupon deleted successfully', 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
