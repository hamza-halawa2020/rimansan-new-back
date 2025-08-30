<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Category;
use App\Services\FileService;

class CategoryService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        return Category::all();
    }

    public function store(array $data)
    {
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->fileService->upload($data['image'], 'images/categories');
        } else {
            $data['image'] = 'default.png';
        }
        return Category::create($data);
    }

    public function show(string $id)
    {
        return Category::findOrFail($id);
    }


    public function update(array $data, string $id)
    {
        $categories = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            if ($categories->image && $categories->image !== 'images/categories/default.png' && file_exists(public_path($categories->image))) {
                $this->fileService->delete($categories->image);
            }

            $data['image'] = $this->fileService->upload($data['image'], 'images/categories');
        } else {
            $data['image'] = $categories->image ?? 'images/categories/default.png';
        }

        $categories->update($data);

        return $categories;
    }


    public function destroy(string $id)
    {

        $categories = $this->show($id);

        if ($categories->image && $categories->image !== 'images/side-bar/default.png' && file_exists(public_path($categories->image))) {
            $this->fileService->delete($categories->image, 'images/side-bar');
        }

        $categories->delete();
        return $categories;
    }
}
