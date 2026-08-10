<?php

namespace App\Services;

use App\Models\SocialLink;

class SocialLinkService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        return SocialLink::all();
    }

    public function store(array $data)
    {
        if (isset($data['icon']) && $data['icon']->isValid()) {
            $data['icon'] = $this->fileService->upload($data['icon'], 'images/socials');
        } else {
            $data['icon'] = 'default.png';
        }

        return SocialLink::create($data);
    }

    public function show(string $id)
    {
        return SocialLink::findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $socialLink = $this->show($id);

        if (isset($data['icon']) && $data['icon']->isValid()) {
            $this->deleteIcon($socialLink->icon);
            $data['icon'] = $this->fileService->upload($data['icon'], 'images/socials');
        }

        $socialLink->update($data);
        return $socialLink;
    }

    public function destroy(string $id)
    {
        $socialLink = $this->show($id);
        $this->deleteIcon($socialLink->icon);
        $socialLink->delete();
        return $socialLink;
    }

    private function deleteIcon(?string $icon): void
    {
        if (!$icon || $icon === 'default.png' || $icon === 'images/socials/default.png') {
            return;
        }

        $path = str_contains($icon, '/') ? $icon : 'images/socials/' . $icon;
        $this->fileService->delete($path);
    }
}
