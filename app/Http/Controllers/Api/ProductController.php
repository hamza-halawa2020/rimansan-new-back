<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Exception;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    function __construct()
    {
        $this->middleware("auth:sanctum")->except(["index", "show", "indexByCategory"]);
    }

    public function index()
    {
        try {
            $products = Product::paginate(10);
            return ProductResource::collection($products);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function indexByCategory($id)
    {
        try {
            $products = Product::where('category_id', $id)->get();
            return ProductResource::collection($products);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $adminId = auth()->id();
                $validatedData['admin_id'] = $adminId;
                $priceAfterDiscount = $request->priceBeforeDiscount - $request->discount;
                $validatedData['priceAfterDiscount'] = $priceAfterDiscount;
                $product = Product::create($validatedData);

                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $image) {
                        $extension = $image->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;
                        $folderPath = 'images/products/';
                        $image->move(public_path($folderPath), $filename);
                        $product->productImages()->create([
                            'product_id' => $product->id,
                            'image' => $filename,
                        ]);
                    }
                }
                return response()->json(['data' => new ProductResource($product)], 201);
            } else {
                return response()->json(['message' => 'not allow to delete product.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            return new ProductResource($product);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $product = Product::findOrFail($id);
                $validatedData = $request->validated();
                if (isset($validatedData['priceBeforeDiscount']) && isset($validatedData['discount'])) {
                    $priceAfterDiscount = $validatedData['priceBeforeDiscount'] - $validatedData['discount'];
                    $validatedData['priceAfterDiscount'] = $priceAfterDiscount;
                }
                $product->update($validatedData);
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $image) {
                        $extension = $image->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;
                        $folderPath = 'images/products/';
                        $image->move(public_path($folderPath), $filename);
                        $product->productImages()->create([
                            'product_id' => $product->id,
                            'image' => $filename,
                        ]);
                    }
                }
                return response()->json(['data' => new ProductResource($product)], 200);
            } else {
                return response()->json(['message' => 'not allowed to update product.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function deleteImage(string $productId, string $imageId)
    {
        try {
            if (Gate::allows("is-admin")) {
                $product = Product::findOrFail($productId);
                $image = $product->productImages()->findOrFail($imageId);

                // Delete the image file if it exists
                $imagePath = 'images/products/' . $image->image;
                if (file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
                $image->delete();

                return response()->json(['message' => 'Image deleted successfully'], 200);
            }

            return response()->json(['message' => 'Unauthorized'], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $product = Product::findOrFail($id);

                foreach ($product->productImages as $image) {
                    $imagePath = public_path('images/products/' . $image->image);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $product->productReviews()->delete();
                $product->productImages()->delete();
                $product->delete();

                return response()->json(['data' => 'product deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete product.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
