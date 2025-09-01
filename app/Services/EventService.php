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
            if ($event->image && $event->image !== 'default.png' && file_exists(public_path('images/events/' . $event->image))) {
                $this->fileService->delete('images/events/' . $event->image);
            }

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
                    if ($eventImage->image && file_exists(public_path('images/events/' . $eventImage->image))) {
                        $this->fileService->delete('images/events/' . $eventImage->image);
                    }
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
        if ($image->image && $image->image !== 'images/events/default.png' && file_exists(public_path($image->image))) {
            $this->fileService->delete($image->image, 'images/events');
        }
        $image->delete();
        return $image;
    }


    public function destroy(string $id)
    {
        $event = $this->show($id);
        $event->delete();
        return $event;
    }
}
