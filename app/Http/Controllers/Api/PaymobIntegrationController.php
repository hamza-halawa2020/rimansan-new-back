<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Services\PaymobIntegrationService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PaymobIntegrationController extends Controller
{
    use ApiResponse;

    private PaymobIntegrationService $paymobIntegrationService;

    public function __construct(PaymobIntegrationService $paymobIntegrationService)
    {
        $this->middleware("auth:sanctum")->only(['index']);
        $this->middleware("limitReq");
        $this->paymobIntegrationService = $paymobIntegrationService;
    }

    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $payments = $this->paymobIntegrationService->index();
                return $this->success(PaymentResource::collection($payments));
            } else {
                return $this->error('not allow to show payments.', 403);
            }
        } catch (Exception $e) {
            Log::error('Error in index', ['message' => $e->getMessage()]);
            return $this->error($e->getMessage(), 500);
        }
    }

    public function state()
    {
        Log::warning('State endpoint called, redirecting to callback', ['query' => request()->all()]);
        return $this->callback(request());
    }

    public function credit(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'orderID' => 'required|exists:orders,id',
            ]);

            $result = $this->paymobIntegrationService->credit($validatedData['orderID']);
            return $this->respondFromPayload($result);
        } catch (Exception $e) {
            Log::error('Exception in credit', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->error($e->getMessage(), 500);
        }
    }

    public function callback(Request $request)
    {
        try {
            $result = $this->paymobIntegrationService->callback($request->getContent(), $request->get('hmac'));
            return $this->respondFromPayload($result);
        } catch (Exception $e) {
            Log::error('Error in callback', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->error($e->getMessage(), 500);
        }
    }

    private function respondFromPayload(array $result)
    {
        if ($result['status'] >= 400) {
            return $this->error($result['payload']['error'] ?? $result['payload']['status'] ?? 'error', $result['status']);
        }

        return $this->success($result['payload']);
    }
}
