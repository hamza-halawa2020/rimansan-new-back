<?php

namespace App\Services;

use App\Models\Event;
use App\Services\FileService;

class EventService
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
    public function index()
    {
        return Event::with('category', 'tag', 'admin', 'eventImages')->latest()->get();
    }

    public function store(array $data)
    {
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->fileService->upload($data['image'], 'images/events');
        } else {
            $data['image'] = 'default.png';
        }

        $event = Event::create($data);

        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                $filename = $this->fileService->upload($image, 'images/events');
                $event->eventImages()->create([
                    'event_id' => $event->id,
                    'image' => $filename,
                ]);
            }
        }


        return $event;
    }

    public function show(string $id)
    {
        return Event::with('category', 'tag', 'admin', 'eventImages')->findOrFail($id);
    }


    public function update(array $data, $id)
    {
        $event = $this->show($id);

        if (isset($data['image']) && $data['image']->isValid()) {
            $this->deleteEventImageFile($event->image);

            $data['image'] = $this->fileService->upload($data['image'], 'images/events');
        } else {
            $data['image'] = $event->image ?? 'default.png';
        }

        $event->update($data);

        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                $filename = $this->fileService->upload($image, 'images/events');
                $event->eventImages()->create([
                    'event_id' => $event->id,
                    'image' => $filename,
                ]);
            }
        }

        if (isset($data['delete_images']) && is_array($data['delete_images'])) {
            foreach ($data['delete_images'] as $imageId) {
                $eventImage = $event->eventImages()->find($imageId);
                if ($eventImage) {
                    $this->deleteEventImageFile($eventImage->image);
                    $eventImage->delete();
                }
            }
        }

        return $event;
    }


    public function deleteImage(string $eventId, string $imageId)
    {
        $event = $this->show($eventId);
        $image = $event->eventImages()->findOrFail($imageId);
        $this->deleteEventImageFile($image->image);
        $image->delete();
        return $image;
    }


    public function destroy(string $id)
    {
        $event = $this->show($id);
        $event->delete();
        return $event;
    }

    private function deleteEventImageFile(?string $image): void
    {
        if (!$image || $image === 'default.png' || $image === 'images/events/default.png') {
            return;
        }

        $path = str_contains($image, '/') ? $image : 'images/events/' . $image;
        $this->fileService->delete($path);
    }
}
