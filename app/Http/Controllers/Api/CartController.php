<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Services\CartService;
use App\Traits\ApiResponse;
use Exception;

class CartController extends Controller
{
    use ApiResponse;

    private $userId;
    private CartService $cartService;
    function __construct(CartService $cartService)
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

        $this->cartService = $cartService;
    }

    public function index()
    {
        try {
            $Carts = $this->cartService->index($this->userId);
            return $this->success(CartResource::collection($Carts));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreCartRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = $this->userId;
            $validatedData['quantity'] = 1;

            $exists = Cart::where('user_id', $this->userId)->where('product_id', $validatedData['product_id'])->exists();
            if ($exists) {
                return $this->error('This product is already in your Carts.', 409);
            }

            $Cart = $this->cartService->store($validatedData);
            return $this->success(new CartResource($Cart), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Cart = $this->cartService->show($id, $this->userId);
            return $this->success(new CartResource($Cart));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCartRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $cart =  $this->cartService->update($validatedData, $id, $this->userId);
            return $this->success(new CartResource($cart), 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $this->cartService->destroy($id, $this->userId);
            return $this->success('Cart deleted successfully', 204);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function clearCart()
    {
        try {
            $this->cartService->clearCart($this->userId);
            return $this->success(null, 'Cart cleared successfully', 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
