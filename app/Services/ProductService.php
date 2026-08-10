<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

class ProductService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        return Product::all();
    }

    public function indexByCategory(string $id)
    {
        return Product::where('category_id', $id)->get();
    }

    public function store(array $data)
    {
        $this->setPriceAfterDiscount($data);
        $images = $data['image'] ?? [];
        unset($data['image']);

        $product = Product::create($data);
        $this->storeImages($product, $images);

        return $product;
    }

    public function show(string $id)
    {
        return Product::findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $product = $this->show($id);
        $this->setPriceAfterDiscount($data);
        $images = $data['image'] ?? [];
        unset($data['image']);

        $product->update($data);
        $this->storeImages($product, $images);

        return $product;
    }

    public function deleteImage(string $productId, string $imageId)
    {
        $product = $this->show($productId);
        $image = $product->productImages()->findOrFail($imageId);
        $this->deleteImageFile($image->image);
        $image->delete();

        return $image;
    }

    public function destroy(string $id)
    {
        $product = $this->show($id);

        foreach ($product->productImages as $image) {
            $this->deleteImageFile($image->image);
        }

        $product->productReviews()->delete();
        $product->productImages()->delete();
        $product->delete();

        return $product;
    }

    private function setPriceAfterDiscount(array &$data): void
    {
        if (isset($data['priceBeforeDiscount']) && isset($data['discount'])) {
            $data['priceAfterDiscount'] = $data['priceBeforeDiscount'] - $data['discount'];
        }
    }

    private function storeImages(Product $product, $images): void
    {
        if (!$images) {
            return;
        }

        if ($images instanceof UploadedFile) {
            $images = [$images];
        }

        foreach ($images as $image) {
            if (!$image->isValid()) {
                continue;
            }

            $filename = $this->fileService->upload($image, 'images/products');

            $product->productImages()->create([
                'product_id' => $product->id,
                'image' => $filename,
            ]);
        }
    }

    private function deleteImageFile(?string $image): void
    {
        if (!$image) {
            return;
        }

        $path = str_contains($image, '/') ? $image : 'images/products/' . $image;
        $this->fileService->delete($path);
    }
}
