<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\ContactUs;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Support\Facades\Gate;
use Exception;


class ContactController extends Controller
{
    function __construct()
    {
        $this->middleware("auth:sanctum")->except('store');
        $this->middleware("limitReq");

    }


    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $contacts = ContactUs::paginate(10);
                return ContactResource::collection($contacts);
            } else {
                return response()->json(['message' => 'not allow to show contacts.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
    public function store(StoreContactRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $contact = ContactUs::create($validatedData);
            return response()->json(['data' => new ContactResource($contact)], 200);
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
    public function show(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $contact = ContactUs::findOrFail($id);
                return new ContactResource($contact);
            } else {
                return response()->json(['message' => 'not allow to show contacts.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $contact = ContactUs::findOrFail($id);
                $contact->delete();
                return response()->json(['message' => 'Contact deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to show contacts.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
}
