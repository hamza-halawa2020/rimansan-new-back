<?php

namespace App\Services;

use App\Models\Course;
use App\Services\FileService;

class CourseService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }


    public function index()
    {
        return Course::with('admin', 'category', 'tag', 'instructor', 'courseReviews')->get();
    }

    public function randomCourses()
    {
        return Course::with('admin', 'category', 'tag', 'instructor', 'courseReviews')->inRandomOrder()->take(3)->get();
    }

    public function store(array $data)
    {
        $this->normalizeCertifications($data);

        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->fileService->upload($data['image'], 'images/Courses');
        } else {
            $data['image'] = 'default.png';
        }
        return Course::create($data);
    }

    public function show(string $id)
    {
        return Course::with('admin', 'category', 'tag', 'instructor', 'courseReviews')->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $course = $this->show($id);
        $this->normalizeCertifications($data);

        if (isset($data['image']) && $data['image']->isValid()) {
            if ($course->image && $course->image !== 'images/Courses/default.png' && file_exists(public_path($course->image))) {
                $this->fileService->delete($course->image);
            }
            $data['image'] = $this->fileService->upload($data['image'], 'images/Courses');
        } else {
            $data['image'] = $course->image ?? 'images/Courses/default.png';
        }

        $course->update($data);
        return $course;
    }

    private function normalizeCertifications(array &$data): void
    {
        if (!array_key_exists('certifications', $data)) {
            return;
        }

        $data['certifications'] = filter_var($data['certifications'], FILTER_VALIDATE_BOOLEAN);
    }


    public function destroy(string $id)
    {
        $course = $this->show($id);

        if ($course->image && $course->image !== 'images/Courses/default.png' && file_exists(public_path($course->image))) {
            $this->fileService->delete($course->image);
        }
        $course->delete();
        return $course;
    }
}
