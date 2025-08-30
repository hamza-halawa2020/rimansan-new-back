<?php

namespace App\Services;

use App\Models\AddSideBarBanner;
use App\Services\FileService;

class AddSideBarBannerService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
    public function index()
    {
        return AddSideBarBanner::where('status', 'active')->with('admin')->get();
    }

    public function all()
    {
        return AddSideBarBanner::with('admin')->paginate(10);
    }

    public function store(array $data)
    {
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->fileService->upload($data['image'], 'images/side-bar');
        } else {
            $data['image'] = 'default.png';
        }

        return AddSideBarBanner::create($data);
    }

    public function show(string $id)
    {
        return AddSideBarBanner::with('admin')->findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $addSideBarBanner = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            if ($addSideBarBanner->image && $addSideBarBanner->image !== 'images/side-bar/default.png' && file_exists(public_path($addSideBarBanner->image))) {
                $this->fileService->delete($addSideBarBanner->image);
            }

            $data['image'] = $this->fileService->upload($data['image'], 'images/side-bar');
        } else {
            $data['image'] = $addSideBarBanner->image ?? 'images/side-bar/default.png';
        }

        $addSideBarBanner->update($data);

        return $addSideBarBanner;
    }


    public function destroy(string $id)
    {
        $addSideBarBanner = $this->show($id);

        if ($addSideBarBanner->image && $addSideBarBanner->image !== 'images/side-bar/default.png' && file_exists(public_path($addSideBarBanner->image))) {
            $this->fileService->delete($addSideBarBanner->image, 'images/side-bar');
        }

        $addSideBarBanner->delete();
        return $addSideBarBanner;
    }
}
