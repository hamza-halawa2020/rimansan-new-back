<?php

namespace App\Services;

use App\Models\MainSlider;
use App\Services\FileService;

class MainSliderService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        return MainSlider::where('status', 'active')->get();
    }

    public function all()
    {
        return MainSlider::paginate(10);
    }

    public function store(array $data)
    {

        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->fileService->upload($data['image'], 'images/main-sliders');
        } else {
            $data['image'] = 'default.png';
        }

        return MainSlider::create($data);
    }

    public function show(string $id)
    {
        return MainSlider::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $mainSlider = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            if ($mainSlider->image && $mainSlider->image !== 'images/main-sliders/default.png' && file_exists(public_path($mainSlider->image))) {
                $this->fileService->delete($mainSlider->image);
            }

            $data['image'] = $this->fileService->upload($data['image'], 'images/main-sliders');
        } else {
            $data['image'] = $mainSlider->image ?? 'images/main-sliders/default.png';
        }

        $mainSlider->update($data);

        return $mainSlider;
    }

    public function destroy(string $id)
    {
        $mainSlider = $this->show($id);

        if ($mainSlider->image && $mainSlider->image !== 'images/main-sliders/default.png' && file_exists(public_path($mainSlider->image))) {
            $this->fileService->delete($mainSlider->image, 'images/main-sliders');
        }
        $mainSlider->delete();
        return $mainSlider;
    }
}
