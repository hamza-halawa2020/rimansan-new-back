<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Support\Facades\Gate;
use Exception;

class ClientController extends Controller
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
                $clients = Client::paginate(10);
                return ClientResource::collection($clients);
            } else {
                return response()->json(['message' => 'not allow to show clients.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(StoreClientRequest $request)
    {
        try {

            $validatedData = $request->validated();
            $client = Client::create($validatedData);
            return response()->json(['data' => new ClientResource($client)], 201);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function show(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {

                $client = Client::findOrFail($id);
                return new ClientResource($client);
            } else {
                return response()->json(['message' => 'not allow to show client.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $client = Client::findOrFail($id);
                $client->delete();
                return response()->json(['data' => 'client deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'not allow to delete client.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
