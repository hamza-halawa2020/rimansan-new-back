<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Gate;

class PaymobIntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:sanctum")->only(['index']);
        $this->middleware("limitReq");
    }

    private $baseUrl = 'https://accept.paymob.com/api';

    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $payments = Payment::orderBy('created_at', 'desc')->paginate(10);
                return PaymentResource::collection($payments);
            } else {
                return response()->json(['message' => 'not allow to show payments.'], 403);
            }
        } catch (Exception $e) {
            Log::error('Error in index', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Remove or disable state method unless needed
    /*
    public function state()
    {
        Log::warning('State endpoint called, redirecting to callback', ['query' => request()->all()]);
        return $this->callback(request());
    }
    */

    public function getToken()
    {
        try {
            Log::info('Attempting to retrieve Paymob authentication token.');
            $response = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => env('PAYMOB_API_KEY')
            ]);

            if ($response->failed()) {
                Log::error('Failed to retrieve Paymob token', ['response' => $response->body()]);
                return null;
            }

            $token = $response->object()->token;
            Log::info('Successfully retrieved Paymob token', ['token' => $token]);
            return $token;
        } catch (Exception $e) {
            Log::error('Error in getToken', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function createPaymentIntention($token, $items, $merchantOrderId, $billingData)
    {
        try {
            // Use the endpoint from Postman collection
            $url = "https://accept.paymob.com/v1/intention/";
            Log::info('Attempting to create Paymob payment intention', [
                'url' => $url,
                'merchantOrderId' => $merchantOrderId,
                'items' => $items,
                'billingData' => $billingData
            ]);

            $data = [
                'amount' => array_sum(array_column($items, 'amount_cents')),
                'currency' => 'EGP',
                'payment_methods' => [(int) env('PAYMOB_INTEGRATION_ID')],
                'items' => array_map(function ($item) {
                    return [
                        'name' => $item['name'],
                        'amount' => $item['amount_cents'],
                        'description' => $item['name'],
                        'quantity' => 1
                    ];
                }, $items),
                'billing_data' => [
                    'apartment' => $billingData['apartment'] ?? 'NA',
                    'first_name' => $billingData['first_name'],
                    'last_name' => $billingData['last_name'],
                    'street' => $billingData['street'] ?? 'NA',
                    'building' => $billingData['building'] ?? 'NA',
                    'phone_number' => $billingData['phone_number'],
                    'city' => $billingData['city'] ?? 'NA',
                    'country' => $billingData['country'] ?? 'NA',
                    'email' => $billingData['email'],
                    'floor' => $billingData['floor'] ?? 'NA',
                    'state' => $billingData['city'] ?? 'NA',
                    'postal_code' => 'NA' // Optional
                ],
                'extras' => [
                    'merchant_order_id' => (string) $merchantOrderId
                ],
                'special_reference' => (string) $merchantOrderId,
                'expiration' => 3600,
                'notification_url' => env('NOTIFICATION_URL'),
                'redirection_url' => env('REDIRECTION_URL')
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . env('SECRET_KEY'),
                'Content-Type' => 'application/json'
            ])->post($url, $data);

            Log::info('Paymob payment intention response', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            if ($response->failed()) {
                Log::error('Failed to create Paymob payment intention', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                return null;
            }

            $paymentIntention = $response->json();
            if (!isset($paymentIntention['id']) || !isset($paymentIntention['client_secret'])) {
                Log::error('Payment intention ID or client_secret missing', ['response' => $response->body()]);
                return null;
            }

            Log::info('Successfully created Paymob payment intention', [
                'intentionId' => $paymentIntention['id'],
                'clientSecret' => $paymentIntention['client_secret']
            ]);
            return $paymentIntention;
        } catch (Exception $e) {
            Log::error('Exception in createPaymentIntention', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function credit(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'orderID' => 'required|exists:orders,id',
            ]);

            $order = Order::find($validatedData['orderID']);
            Log::info('Processing credit request', ['orderID' => $order->id, 'order' => $order]);

            if (!$order) {
                Log::error('Order not found', ['orderID' => $validatedData['orderID']]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            $billingData = [
                'apartment' => 'NA',
                'email' => $order->user->email ?? $order->client->email,
                'floor' => 'NA',
                'first_name' => $order->user->name ?? $order->client->name,
                'street' => $order->address->address ?? 'NA',
                'building' => 'NA',
                'phone_number' => $order->user->phone ?? $order->client->phone,
                'city' => $order->address->city->name ?? 'NA',
                'country' => $order->address->country->name ?? 'NA',
                'last_name' => $order->user->name ?? $order->client->name,
            ];
            Log::info('Billing data prepared', ['billingData' => $billingData]);

            $localOrders = Payment::where('order_id', $order->id)->get();
            Log::info('Local orders fetched', ['localOrders' => $localOrders]);

            foreach ($localOrders as $localOrder) {
                if ($localOrder->status === 'completed') {
                    Log::info('Completed payment found', ['payment' => $localOrder]);
                    return response()->json([
                        'id' => $localOrder->id,
                        'order' => $localOrder->order,
                        'payment_method' => $localOrder->payment_method,
                        'amount' => $localOrder->amount,
                        'status' => $localOrder->status,
                        'notes' => $localOrder->notes,
                        'paymob_order_id' => $localOrder->paymob_order_id,
                        'transaction_id' => $localOrder->transaction_id,
                    ]);
                }
            }

            foreach ($localOrders as $localOrder) {
                if ($localOrder->status === 'pending') {
                    $localOrder->update(['status' => 'failed']);
                    Log::info('Pending payment marked as failed', ['paymentId' => $localOrder->id]);
                }
            }

            $newPayment = Payment::create([
                'status' => 'pending',
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'amount' => $order->total_price,
                'notes' => 'Payment initiated for order #' . $order->order_number,
            ]);
            Log::info('New payment created', ['payment' => $newPayment]);

            $items = [
                [
                    'name' => $order->order_number,
                    'amount_cents' => $order->total_price * 100,
                ]
            ];
            Log::info('Items prepared for payment', ['items' => $items]);

            $token = $this->getToken();
            Log::info('Authentication token retrieved', ['token' => $token]);
            if (!$token) {
                Log::error('Failed to retrieve authentication token');
                return response()->json(['error' => 'Failed to retrieve authentication token'], 500);
            }

            $merchantOrderId = $newPayment->id;
            Log::info('Merchant order ID set', ['merchantOrderId' => $merchantOrderId]);

            $paymentIntention = $this->createPaymentIntention($token, $items, $merchantOrderId, $billingData);
            Log::info('Payment intention response', ['paymentIntention' => $paymentIntention]);
            if (!$paymentIntention) {
                Log::error('Failed to create payment intention');
                return response()->json(['error' => 'Failed to create payment intention'], 500);
            }

            $newPayment->update([
                'paymob_order_id' => $paymentIntention['intention_order_id'] ?? null,
                'transaction_id' => $paymentIntention['id'],
            ]);
            Log::info('Payment updated with intention details', ['payment' => $newPayment]);

            // Use client_secret and public_key for unified checkout (per Postman)
            $clientSecret = $paymentIntention['client_secret'] ?? null;
            if (!$clientSecret) {
                Log::error('Client secret not found', ['paymentIntention' => $paymentIntention]);
                return response()->json(['error' => 'Client secret not found'], 500);
            }

            $publicKey = env('PAYMOB_PUBLIC_KEY'); // Add PAYMOB_PUBLIC_KEY to .env
            if (!$publicKey) {
                Log::error('Public key not configured');
                return response()->json(['error' => 'Public key not configured'], 500);
            }

            $iframeUrl = "https://accept.paymob.com/unifiedcheckout/?publicKey={$publicKey}&clientSecret={$clientSecret}";
            Log::info('Iframe URL generated', ['iframeUrl' => $iframeUrl]);

            return response()->json([
                'iframe_url' => $iframeUrl,
            ]);
        } catch (Exception $e) {
            Log::error('Exception in credit', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function callback(Request $request)
    {
        try {
            Log::info('Callback received', ['request' => $request->getContent()]);

            $data = json_decode($request->getContent(), true);
            if (!$data) {
                Log::error('Invalid JSON payload', ['payload' => $request->getContent()]);
                return response()->json(['error' => 'Invalid JSON payload'], 400);
            }

            $fields = [
                'amount_cents' => $data['obj']['amount_cents'] ?? null,
                'created_at' => $data['obj']['created_at'] ?? null,
                'currency' => $data['obj']['currency'] ?? null,
                'error_occured' => $data['obj']['error_occured'] ?? null,
                'has_parent_transaction' => $data['obj']['has_parent_transaction'] ?? null,
                'id' => $data['obj']['id'] ?? null,
                'integration_id' => $data['obj']['integration_id'] ?? null,
                'is_3d_secure' => $data['obj']['is_3d_secure'] ?? null,
                'is_auth' => $data['obj']['is_auth'] ?? null,
                'is_capture' => $data['obj']['is_capture'] ?? null,
                'is_refunded' => $data['obj']['is_refunded'] ?? null,
                'is_standalone_payment' => $data['obj']['is_standalone_payment'] ?? null,
                'is_voided' => $data['obj']['is_voided'] ?? null,
                'order_id' => $data['obj']['order']['id'] ?? null,
                'owner' => $data['obj']['owner'] ?? null,
                'pending' => $data['obj']['pending'] ?? null,
                'source_data_pan' => $data['obj']['source_data']['pan'] ?? null,
                'source_data_sub_type' => $data['obj']['source_data']['sub_type'] ?? null,
                'source_data_type' => $data['obj']['source_data']['type'] ?? null,
                'success' => $data['obj']['success'] ?? null,
            ];

            Log::info('Extracted fields', ['fields' => $fields]);

            $connectedString = $this->generateConnectedString($fields);
            Log::info('Generated connected string', ['connectedString' => $connectedString]);

            $secure_hash = $request->get('hmac');
            $secret = env('PAYMOB_HMAC_SECRET');
            $hashed = hash_hmac('SHA512', $connectedString, $secret);
            Log::info('Hash comparison', ['generated_hash' => $hashed, 'received_hash' => $secure_hash]);

            if ($hashed !== $secure_hash) {
                Log::warning('HMAC mismatch', ['status' => 'not secure']);
                return response()->json(['status' => 'not secure'], 200);
            }

            $localOrderId = $data['obj']['order']['merchant_order_id'] ?? $data['obj']['order']['special_reference'] ?? null;
            if (!$localOrderId) {
                Log::error('merchant_order_id or special_reference missing', ['data' => $data]);
                return response()->json(['status' => 'merchant_order_id missing'], 400);
            }

            Log::info('Merchant order ID', ['localOrderId' => $localOrderId]);

            $payment = Payment::find($localOrderId);
            if (!$payment) {
                Log::error('Payment not found', ['localOrderId' => $localOrderId]);
                return response()->json(['status' => 'payment not found'], 404);
            }

            Log::info('Payment found', ['payment' => $payment]);

            $payment->update([
                'status' => $data['obj']['success'] === true ? 'completed' : 'failed',
                'transaction_id' => $data['obj']['id'],
                'paymob_order_id' => $data['obj']['order']['id'],
            ]);

            if ($data['obj']['success'] === true) {
                $payment->order->update([
                    'status' => 'Pending',
                ]);
            } else {
                $payment->order->delete();
            }

            Log::info('Payment updated', ['payment' => $payment]);

            return response()->json(['status' => 'callback handled'], 200);
        } catch (Exception $e) {
            Log::error('Error in callback', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateConnectedString(array $data): string
    {
        try {
            return implode('', [
                $data['amount_cents'],
                $data['created_at'],
                $data['currency'],
                $data['error_occured'] ? 'true' : 'false',
                $data['has_parent_transaction'] ? 'true' : 'false',
                $data['id'],
                $data['integration_id'],
                $data['is_3d_secure'] ? 'true' : 'false',
                $data['is_auth'] ? 'true' : 'false',
                $data['is_capture'] ? 'true' : 'false',
                $data['is_refunded'] ? 'true' : 'false',
                $data['is_standalone_payment'] ? 'true' : 'false',
                $data['is_voided'] ? 'true' : 'false',
                $data['order_id'],
                $data['owner'],
                $data['pending'] ? 'true' : 'false',
                $data['source_data_pan'],
                $data['source_data_sub_type'],
                $data['source_data_type'],
                $data['success'] ? 'true' : 'false',
            ]);
        } catch (Exception $e) {
            Log::error('Error in generateConnectedString', ['message' => $e->getMessage()]);
            return '';
        }
    }
}