<?php

namespace App\Services;

use App\Models\Favourite;

class FavouriteService
{
    public function index(int $userId)
    {
        return Favourite::where('user_id', $userId)->with('product')->get();
    }

    public function store(array $data, int $userId)
    {
        $exists = Favourite::where('user_id', $userId)->where('product_id', $data['product_id'])->exists();

        if ($exists) {
            return null;
        }

        $data['user_id'] = $userId;
        return Favourite::create($data);
    }

    public function show(int $id, int $userId)
    {
        return Favourite::where('user_id', $userId)->with('product')->findOrFail($id);
    }

    public function destroy(int $id, int $userId)
    {
        $favourite = $this->show($id, $userId);
        $favourite->delete();
        return $favourite;
    }

    public function clearFav(int $userId)
    {
        return Favourite::where('user_id', $userId)->delete();
    }
}
