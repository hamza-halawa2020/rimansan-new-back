<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class CartController extends Controller
{


    private $userId;
    function __construct()
    {
        $this->middleware("auth:sanctum");
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $Carts = Cart::where('user_id', $this->userId)->paginate(10);
            return CartResource::collection($Carts);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
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
                return response()->json(['message' => 'This product is already in your Carts.'], 409);
            }

            $Cart = Cart::create($validatedData);
            return response()->json(['data' => new CartResource($Cart),], 201);
        } catch (Exception $e) {
            Log::error('Failed to add product to cart: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add Cart. Please try again later.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Cart = Cart::where('user_id', $this->userId)->findOrFail($id);
            return new CartResource($Cart);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateCartRequest $request, string $id)
    {
        try {
            $validatedData = $request->validated();
            $Cart = Cart::where('user_id', $this->userId)->findOrFail($id);
            $Cart->update($validatedData);
            return response()->json(['data' => new CartResource($Cart)], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $Cart = Cart::where('user_id', $this->userId)->findOrFail($id);
            $Cart->delete();
            return response()->json(['data' => 'Cart deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function clearCart()
    {
        try {
            Cart::where('user_id', $this->userId)->delete();
            return response()->json(['message' => 'Cart cleared successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
