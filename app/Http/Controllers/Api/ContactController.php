<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Http\Requests\StoreContactRequest;
use App\Services\ContactService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Gate;
use Exception;

class ContactController extends Controller
{
    use ApiResponse;
    private ContactService $contactService;

    function __construct(ContactService $contactService)
    {
        $this->middleware("auth:sanctum")->except('store');
        $this->middleware("limitReq");

        $this->contactService = $contactService;
    }

    public function index()
    {
        try {
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to show contacts.', 403);
            }
            $contacts = $this->contactService->index();
            return $this->success(ContactResource::collection($contacts));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreContactRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $contact = $this->contactService->store($validatedData);
            return $this->success(new ContactResource($contact), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to show contacts.', 403);
            }

            $contact = $this->contactService->show($id);
            return $this->success(new ContactResource($contact));
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (!Gate::allows("is-admin")) {
                return $this->error('not allow to delete contacts.', 403);
            }

            $this->contactService->destroy($id);
            return $this->success('Contact deleted successfully', 200);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
