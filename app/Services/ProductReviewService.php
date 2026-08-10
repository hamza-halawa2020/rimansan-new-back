<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ProductReview;

class ProductReviewService
{
    public function index()
    {
        return ProductReview::where('status', 'active')->orderBy('created_at', 'desc')->get();
    }

    public function all()
    {
        return ProductReview::orderBy('created_at', 'desc')->paginate(10);
    }

    public function store(array $data, int $userId)
    {
        $data['user_id'] = $userId;
        return ProductReview::create($data);
    }

    public function storeByClient(array $data)
    {
        $client = Client::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $data['client_id'] = $client->id;
        return ProductReview::create($data);
    }

    public function show(string $id)
    {
        return ProductReview::where('status', 'active')->findOrFail($id);
    }

    public function showAll(string $id)
    {
        return ProductReview::findOrFail($id);
    }

    public function active(array $data, string $id, int $adminId)
    {
        $data['admin_id'] = $adminId;
        $review = $this->showAll($id);
        $review->update($data);
        return $review;
    }

    public function update(array $data, string $id)
    {
        $review = $this->showAll($id);
        $review->update($data);
        return $review;
    }

    public function destroy(string $id)
    {
        $review = $this->showAll($id);
        $review->delete();
        return $review;
    }
}
