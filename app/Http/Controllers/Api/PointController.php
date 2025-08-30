<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPointRequest;
use App\Http\Requests\UpdateProductPointRequest;
use App\Http\Resources\ProductPointResource;
use App\Models\ProductPoint;
use Exception;
use Illuminate\Support\Facades\Gate;

class PointController extends Controller
{

    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });

    }

    public function index()
    {
    try {
            $points = ProductPoint::with('product','createdBy')->latest()->get();
            return ProductPointResource::collection($points);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreProductPointRequest $request)
{
    try {
        $validatedData = $request->validated();
        $validatedData['created_by'] = $this->userId;

        if (Gate::allows("is-admin")) {
            ProductPoint::where('product_id', $validatedData['product_id'])->whereNull('disabled_at')->update(['disabled_at' => now()]);
            $point = ProductPoint::create($validatedData);
            return response()->json(['data' => new ProductPointResource($point)], 200);
        } else {
            return response()->json(['message' => 'Not allowed to store point.'], 403);
        }
    } catch (Exception $e) {
        return response()->json($e->getMessage(), 500);
    }
}


    public function show(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
            $point = ProductPoint::with('product','createdBy')->findOrFail($id);
            return new ProductPointResource($point);
            } else {
                return response()->json(['message' => 'not allow to show Point.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    // public function update(UpdateProductPointRequest $request, string $id)
    // {
    //     try {
    //         $validatedData = $request->validated();
    //         $validatedData['created_by'] = $this->userId;
    //         if (Gate::allows("is-admin")) {
    //             $point = ProductPoint::with('product','createdBy')->findOrFail($id);
    //             $point->update($validatedData);
    //             return response()->json(['data' => new ProductPointResource($point)], 200);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    // public function destroy(string $id)
    // {
    //     try {
    //         if (Gate::allows("is-admin")) {
    //             $point = ProductPoint::with('product','createdBy')->findOrFail($id);
    //             $point->delete();
    //             return response()->json(['data' => 'Point deleted successfully'], 200);
    //         } else {
    //             return response()->json(['message' => 'not allow to delete Point.'], 403);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json($e->getMessage(), 500);
    //     }
    // }
}
