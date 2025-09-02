<?php

namespace App\Services;

use App\Models\Instructor;
use App\Services\FileService;

class InstructorService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        return Instructor::with('admin', 'courses')->get();
    }

    public function randomInstructors()
    {
        return Instructor::with('admin', 'courses')->inRandomOrder()->take(4)->get();
    }

    public function store(array $data)
    {

        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->fileService->upload($data['image'], 'images/instructors');
        } else {
            $data['image'] = 'default.png';
        }

        return Instructor::create($data);
    }

    public function show(string $id)
    {
        return Instructor::with('admin', 'courses')->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $instructor = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            if ($instructor->image && $instructor->image !== 'images/instructors/default.png' && file_exists(public_path($instructor->image))) {
                $this->fileService->delete($instructor->image);
            }

            $data['image'] = $this->fileService->upload($data['image'], 'images/instructors');
        } else {
            $data['image'] = $instructor->image ?? 'images/instructors/default.png';
        }

        $instructor->update($data);

        return $instructor;
    }

    public function destroy(string $id)
    {
        $instructor = $this->show($id);

        if ($instructor->image && $instructor->image !== 'images/instructors/default.png' && file_exists(public_path($instructor->image))) {
            $this->fileService->delete($instructor->image, 'images/instructors');
        }
        $instructor->delete();
        return $instructor;
    }
}
