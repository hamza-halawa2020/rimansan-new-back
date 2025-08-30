<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Gate;
use Exception;

class ClientController extends Controller
{
    use ApiResponse;

    private ClientService $clientService;

    function __construct(ClientService $clientService)
    {
        $this->middleware("auth:sanctum")->except('store');
        $this->middleware("limitReq");
        $this->clientService =  $clientService;
    }
    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $clients = $this->clientService->index();
                return $this->success(ClientResource::collection($clients));
            } else {
                return $this->error('not allow to show clients.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreClientRequest $request)
    {
        try {

            $validatedData = $request->validated();
            $client = $this->clientService->store($validatedData);
            return $this->success(new ClientResource($client), 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function show(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $client = $this->clientService->show($id);
                return $this->success(new ClientResource($client));
            } else {
                return $this->error('not allow to show client.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            if (Gate::allows("is-admin")) {
                $client = $this->clientService->destroy($id);
                return $this->success('client deleted successfully', 200);
            } else {
                return  $this->error('not allow to delete client.', 403);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
