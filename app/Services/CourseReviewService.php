<?php

namespace App\Services;

use App\Models\CourseReview;

class CourseReviewService
{


    public function index()
    {
        return CourseReview::where('status', 'active')->with('user', 'course')->latest()->get();
    }

    public function all()
    {
        return CourseReview::with('user', 'course')->latest()->get();
    }

    public function store(array $data)
    {
        return CourseReview::create($data);
    }

    public function show(string $id)
    {
        return  CourseReview::where('status', 'active')->with('user', 'course')->findOrFail($id);
    }
    public function showAll(string $id)
    {
        return  CourseReview::with('user', 'course')->findOrFail($id);
    }

    public function active(array $data, string $id)
    {
        $review = $this->showAll($id);
        $review->update($data);
        return $review;
    }

    public function update(array $data, string $id)
    {
        $review =  $this->showAll($id);
        $review->update($data);
        return $review;
    }

    public function destroy(string $id)
    {
        $review =  $this->showAll($id);
        $review->delete();
        return $review;
    }
}
