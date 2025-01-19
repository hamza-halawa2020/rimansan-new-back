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
    function __construct()
    {
        $this->middleware("auth:sanctum")->only(['index']);
        $this->middleware("limitReq");
    }
    private $baseUrl = 'https://accept.paymob.com/api';


    public function index()
    {
        try {
            if (Gate::allows("is-admin")) {
                $payments = Payment::paginate(10);
                return PaymentResource::collection($payments);
            } else {
                return response()->json(['message' => 'not allow to show payments.'], 403);
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function state()
    {
        try {
            $data = request()->query();
            $hmac = $data['hmac'] ?? null;
            $fields = [
                'amount_cents' => $data['amount_cents'] ?? null,
                'created_at' => $data['created_at'] ?? null,
                'currency' => $data['currency'] ?? null,
                'error_occured' => $data['error_occured'] ?? null,
                'has_parent_transaction' => $data['has_parent_transaction'] ?? null,
                'id' => $data['id'] ?? null,
                'integration_id' => $data['integration_id'] ?? null,
                'is_3d_secure' => $data['is_3d_secure'] ?? null,
                'is_auth' => $data['is_auth'] ?? null,
                'is_capture' => $data['is_capture'] ?? null,
                'is_refunded' => $data['is_refunded'] ?? null,
                'is_standalone_payment' => $data['is_standalone_payment'] ?? null,
                'is_voided' => $data['is_voided'] ?? null,
                'order' => $data['order'] ?? null,
                'owner' => $data['owner'] ?? null,
                'pending' => $data['pending'] ?? null,
                'source_data_pan' => $data['source_data_pan'] ?? null,
                'source_data_sub_type' => $data['source_data_sub_type'] ?? null,
                'source_data_type' => $data['source_data_type'] ?? null,
                'success' => $data['success'] ?? null,
            ];
            foreach ($fields as $field => $value) {
                if (is_null($value)) {
                    Log::warning("Field '{$field}' is null or missing");
                }
            }
            $request_string = implode('', $fields);
            $secret_key = env('PAYMOB_HMAC_SECRET');
            $hashed = hash_hmac('SHA512', $request_string, $secret_key);
            if ($hmac !== $hashed) {
                return response()->json(['error' => 'Invalid HMAC'], 403);
            }
            return response()->json([
                'message' => 'Payment processed successfully',
                'success' => $fields['success'],
                'pending' => $fields['pending'],
            ], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function getToken()
    {
        try {
            Log::info('Attempting to retrieve Paymob authentication token.');
            $response = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => env('PAYMOB_API_KEY')
            ]);
            if ($response->failed()) {
                Log::error('Failed to retrieve Paymob token.', ['response' => $response->body()]);
                return null;
            }
            $returnResponse = $response->object()->token;
            Log::info('Successfully retrieved Paymob token.', ['token' => $returnResponse]);
            return $returnResponse;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    public function createOrder($token, $items, $merchantOrderId)
    {

        try {
            Log::info('Attempting to create Paymob order.', [
                'merchantOrderId' => $merchantOrderId,
                'items' => $items
            ]);

            $data = [
                "auth_token" => $token,
                "delivery_needed" => "false",
                "amount_cents" => array_sum(array_column($items, 'amount_cents')),
                "currency" => "EGP",
                "items" => $items,
                "merchant_order_id" => $merchantOrderId,
            ];

            $response = Http::post("{$this->baseUrl}/ecommerce/orders", $data);

            if ($response->failed()) {
                Log::error('Failed to create Paymob order.', ['response' => $response->body()]);
                return null;
            }

            $paymobOrder = $response->object();
            if (!isset($paymobOrder->id)) {
                Log::error('Paymob order ID is missing in the response.', ['response' => $response->body()]);
                return null;
            }

            Log::info('Successfully created Paymob order.', ['paymobOrderId' => $paymobOrder->id]);
            return $paymobOrder;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    public function getPaymentToken($order, $token, $billingData)
    {
        try {
            $data = [
                "auth_token" => $token,
                "amount_cents" => $order->amount_cents,
                "expiration" => 3600,
                "order_id" => $order->id,
                "billing_data" => $billingData,
                "currency" => "EGP",
                "integration_id" => env('PAYMOB_INTEGRATION_ID')
            ];
            $response = Http::post("{$this->baseUrl}/acceptance/payment_keys", $data);
            return $response->object()->token;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function credit(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'orderID' => 'required|exists:orders,id',
            ]);
            $orderID = Order::find($validatedData['orderID']);
            Log::error('orderID.', ['response' => $orderID]);

            if (!$orderID) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $billingData = [
                "apartment" => 0,
                "email" => $orderID->user->email ?? $orderID->client->email,
                "floor" => 0,
                "first_name" => $orderID->user->name ?? $orderID->client->name,
                "street" => $orderID->address->address,
                "building" => 0,
                "phone_number" => $orderID->user->phone ?? $orderID->client->phone,
                "city" => $orderID->address->city->name,
                "country" => $orderID->address->country->name,
                "last_name" => $orderID->user->name ?? $orderID->client->name,
            ];
            Log::error('billingData.', ['response' => $billingData]);


            $localOrders = Payment::where('order_id', $orderID->id)->get();
            Log::error('localOrders.', ['response' => $localOrders]);

            foreach ($localOrders as $localOrder) {
                if ($localOrder->status === 'completed') {
                    Log::error('Completed order found.', ['response' => $localOrder]);
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
                if ($localOrder->status == 'pending') {
                    $localOrder->update([
                        'status' => 'failed'
                    ]);
                }
            }

            $newPayment = Payment::create([
                'status' => 'pending',
                'order_id' => $orderID->id,
                'payment_method' => $orderID->payment_method,
                'amount' => $orderID->total_price,
                'notes' => 'Payment initiated for order #' . $orderID->order_number,
            ]);

            $items = [
                [
                    "name" => $orderID->order_number,
                    "amount_cents" => $orderID->total_price * 100,
                ]
            ];
            Log::error('items', ['response' => $items]);
            $token = $this->getToken();
            Log::error('token', ['response' => $token]);
            $merchantOrderId = $newPayment->id;
            Log::error('merchantOrderId', ['response' => $merchantOrderId]);
            $order = $this->createOrder($token, $items, $merchantOrderId);
            Log::error('order', ['response' => $order]);
            $paymentToken = $this->getPaymentToken($order, $token, $billingData);
            if (!$paymentToken) {
                return response()->json(['error' => 'Payment token generation failed'], 500);
            }
            return response()->json([
                'iframe_url' => "{$this->baseUrl}/acceptance/iframes/" . env('PAYMOB_IFRAME_ID') . '?payment_token=' . $paymentToken,
            ]);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
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
            $hased = hash_hmac('SHA512', $connectedString, $secret);
            Log::info('Hash comparison', ['generated_hash' => $hased, 'received_hash' => $secure_hash]);

            if ($hased !== $secure_hash) {
                Log::warning('HMAC mismatch', ['status' => 'not secure']);
                return response()->json(['status' => 'not secure'], 200);
            }

            $localOrderId = $data['obj']['order']['merchant_order_id'] ?? null;
            if (!$localOrderId) {
                Log::error('merchant_order_id missing', ['data' => $data]);
                return response()->json(['status' => 'merchant_order_id missing'], 400);
            }

            Log::info('Merchant order ID', ['localOrderId' => $localOrderId]);

            $payment = Payment::find($localOrderId);
            if (!$payment) {
                Log::error('Order not found', ['localOrderId' => $localOrderId]);
                return response()->json(['status' => 'payment not found'], 404);
            }

            Log::info('Order found', ['payment' => $payment]);

            $payment->update([
                'status' => $data['obj']['success'] === true ? 'completed' : 'failed',
                'transaction_id' => $data['obj']['id'],
                'paymob_order_id' => $data['obj']['order']['id'],
            ]);
            $payment->order->update([
                'status' => $data['obj']['success'] === true ? 'Pending' : 'Awaiting Payment',
            ]);
            Log::info('Payment updated', ['payment' => $payment]);

            return response()->json(['status' => 'callback handled'], 200);
        } catch (Exception $e) {
            Log::error('Exception occurred', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json($e->getMessage(), 500);
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
            return response()->json($e->getMessage(), 500);
        }
    }
}
