<?php

namespace App\Services;

use App\Models\Cart;

class CartService
{

    public function index($userId)
    {
        return Cart::where('user_id', $userId)->with('product')->get();
    }

    public function store(array $data)
    {
        return Cart::create($data);
    }

    public function show(string $id, $userId)
    {
        return Cart::where('user_id', $userId)->where('id', $id)->with('product')->firstOrFail();
    }


    public function update(array $data, string $id, $userId)
    {
        $cart = $this->show($id, $userId);
        $cart->update($data);
        return $cart;
    }


    public function destroy(string $id, $userId)
    {
        $cart = $this->show($id, $userId);
        $cart->delete();
        return $cart;
    }

    public function clearCart($userId)
    {
        return Cart::where('user_id', $userId)->delete();
    }
}
