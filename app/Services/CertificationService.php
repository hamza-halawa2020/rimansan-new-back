<?php

namespace App\Services;

use App\Models\Certification;
use App\Services\FileService;

class CertificationService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
    public function index()
    {
        return Certification::with('user')->all();
    }



    public function store(array $data)
    {
        if (isset($data['file']) && $data['file']->isValid()) {
            $data['file'] = $this->fileService->upload($data['file'], 'images/Certifications');
        } else {
            $data['file'] = 'default.png';
        }

        return Certification::create($data);
    }

    public function show(string $id)
    {
        return Certification::with('user')->findOrFail($id);
    }

    public function update(array $data, string $id)
    {
        $certification = $this->show($id);

        if (isset($data['file']) && $data['file']->isValid()) {
            if ($certification->file  && $certification->file  !== 'images/Certifications/default.png' && file_exists(public_path($certification->file ))) {
                $this->fileService->delete($certification->file);
            }

            $data['file'] = $this->fileService->upload($data['file'], 'images/Certifications');
        } else {
            $data['file'] = $certification->file ?? 'images/Certifications/default.png';
        }

        $certification->update($data);

        return $certification;
    }

    public function destroy(string $id)
    {
        $certification = $this->show($id);
        if ($certification->file  && $certification->file  !== 'images/Certifications/default.png' && file_exists(public_path($certification->file ))) {
            $this->fileService->delete($certification->file , 'images/Certifications');
        }
        $certification->delete();
        return $certification;
    }

    public function showBySerialNumber(string $serialNumber)
    {
        return Certification::where('serial_number', $serialNumber)->with('user')->first();
    }

    public function downloadFile(string $id): array
    {
        $certification = $this->show($id);
        return [
            'path' => public_path('images/Certifications/' . $certification->file),
            'filename' => $certification->file
        ];
    }
}
