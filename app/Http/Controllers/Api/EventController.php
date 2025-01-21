<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Exception;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    private $userId;

    function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $Events = Event::paginate(10);
            return EventResource::collection($Events);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreEventRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['admin_id'] = $this->userId;
            if (Gate::allows("is-admin")) {

                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $folderPath = 'images/events/';
                    $image->move(public_path($folderPath), $filename);
                }
                $validatedData['image'] = $filename ?? 'default.png';
                $Event = Event::create($validatedData);

                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $image) {
                        $extension = $image->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;
                        $folderPath = 'images/events/';
                        $image->move(public_path($folderPath), $filename);
                        $Event->eventImages()->create([
                            'event_id' => $Event->id,
                            'image' => $filename,
                        ]);
                    }
                }

                return response()->json(['data' => new EventResource($Event)], 200);
            } else {
                return response()->json(['message' => 'not allow to Store Event.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Event = Event::findOrFail($id);
            return new EventResource($Event);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(UpdateEventRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $Event = Event::findOrFail($id);

                // Process the main event image if it exists
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $image) {
                        if (is_array($image)) { // Fix: Ensure correct iteration
                            foreach ($image as $file) {
                                $this->processImage($file, $Event);
                            }
                        } else {
                            $this->processImage($image, $Event);
                        }
                    }
                }
                $Event->update($validatedData);


                return response()->json(['data' => new EventResource($Event)], 200);
            }

            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteImage(string $eventId, string $imageId)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Event = Event::findOrFail($eventId);
                $image = $Event->eventImages()->findOrFail($imageId);

                // Delete the image file if it exists
                $imagePath = 'images/events/' . $image->image;
                if (file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }

                // Delete the image record from the database
                $image->delete();

                return response()->json(['message' => 'Image deleted successfully'], 200);
            }

            return response()->json(['message' => 'Unauthorized'], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Helper function to process and save each image
    private function processImage($file, $Event)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $folderPath = 'images/events/';
        $file->move(public_path($folderPath), $filename);

        // Save the image in the database
        $Event->eventImages()->create([
            'event_id' => $Event->id,
            'image' => $filename,
        ]);
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $Event = Event::findOrFail($id);
                if ($Event->image && $Event->image !== 'images/Events/default.png' && file_exists(public_path($Event->image))) {
                    unlink(public_path($Event->image));
                }
                $Event->delete();
                return response()->json(['data' => 'Event deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete Event.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
