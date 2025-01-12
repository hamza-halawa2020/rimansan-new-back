<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavouriteRequest;
use App\Http\Resources\FavouriteResource;
use App\Models\Favourite;
use Exception;

class FavouriteController extends Controller
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
            $favourites = Favourite::where('user_id', $this->userId)->paginate(10);
            return FavouriteResource::collection($favourites);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreFavouriteRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = $this->userId;

            $exists = Favourite::where('user_id', $this->userId)->where('product_id', $validatedData['product_id'])->exists();
            if ($exists) {
                return response()->json(['message' => 'This product is already in your favourites.'], 409);
            }

            $favourite = Favourite::create($validatedData);
            return response()->json(['data' => new FavouriteResource($favourite),], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to add favourite. Please try again later.'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $favourite = Favourite::where('user_id', $this->userId)->findOrFail($id);
            return new FavouriteResource($favourite);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $favourite = Favourite::where('user_id', $this->userId)->findOrFail($id);
            $favourite->delete();
            return response()->json(['data' => 'favourite deleted successfully'], 200);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
