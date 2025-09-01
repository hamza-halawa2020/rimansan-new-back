<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use ApiResponse;
    private EventService $eventService;
    private $userId;

    function __construct(EventService $eventService)
    {
        $this->middleware("auth:sanctum")->except(['index', 'show']);
        $this->eventService = $eventService;
        $this->middleware("limitReq");
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $Events = $this->eventService->index();
            return  $this->success(EventResource::collection($Events), 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreEventRequest $request)
    {
        // dd($request->validated());
        try {
            $validatedData = $request->validated();
            $validatedData['admin_id'] = $this->userId;
            if (Gate::allows("is-admin")) {
                $event = $this->eventService->store($validatedData);
                return $this->success(new EventResource($event), 200);
            } else {
                return $this->error('not allow to Store Event.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Event = $this->eventService->show($id);
            return $this->success(new EventResource($Event));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(UpdateEventRequest $request, $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $validatedData = $request->validated();
                $event = $this->eventService->update($validatedData, $id);
                return $this->success(new EventResource($event), 200);
            }
            return $this->error('Unauthorized access', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function deleteImage(string $eventId, string $imageId)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->eventService->deleteImage($eventId, $imageId);
                return $this->success('Image deleted successfully', 200);
            }

            return $this->error('Unauthorized', 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $this->eventService->destroy($id);
                return $this->success(['message' => 'Event deleted successfully'], 200);
            } else {
                return $this->error('not allow to delete Event.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
