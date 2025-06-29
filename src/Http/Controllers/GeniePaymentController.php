<?php

namespace Botble\GeniePayment\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\GeniePayment\Http\Requests\GeniePaymentCallbackRequest;
use Botble\GeniePayment\Services\Gateways\GeniePaymentService;
use Botble\GeniePayment\Models\GenieTransaction;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GeniePaymentController extends Controller
{
    protected GeniePaymentService $geniePaymentService;

    public function __construct(GeniePaymentService $geniePaymentService)
    {
        $this->geniePaymentService = $geniePaymentService;
    }

    /**
     * Handle payment callback from Genie Business
     */
    public function getCallback(
        GeniePaymentCallbackRequest $request,
        BaseHttpResponse $response
    ) {
        try {
            DB::beginTransaction();
            
            $transactionId = $request->input('transactionId');
            $signature = $request->input('signature');
            $state = $request->input('state');
            $amount = $request->input('amount');
            $currency = $request->input('currency');

            if ($this->geniePaymentService->isSandbox()) {
                Log::info('Genie Payment Callback (Sandbox)', $request->all());
            }

            // Validate the payment status
            $status = $this->geniePaymentService->getPaymentStatus($request);

            if (!$status) {
                Log::warning('Genie Payment Callback - Invalid payment status', [
                    'transaction_id' => $transactionId,
                    'request_data' => $request->all()
                ]);

                DB::rollBack();
                return $response
                    ->setError()
                    ->setNextUrl(PaymentHelper::getCancelURL())
                    ->setMessage(__('Payment verification failed. Please try again.'));
            }

            // Update transaction record
            $transaction = GenieTransaction::findByTransactionId($transactionId);
            if ($transaction) {
                $transaction->update([
                    'status' => strtolower($status),
                    'verified_at' => now(),
                    'api_response' => $request->all(),
                ]);
            }

            if ($status === 'COMPLETED') {
                // Process successful payment
                $this->geniePaymentService->afterMakePayment($request->input());

                DB::commit();
                
                return $response
                    ->setNextUrl(PaymentHelper::getRedirectURL())
                    ->setMessage(__('Payment completed successfully!'));
            } else {
                // Handle failed payment
                if ($transaction) {
                    $transaction->markAsFailed('Payment callback indicated failure: ' . $state);
                }

                DB::rollBack();
                
                return $response
                    ->setError()
                    ->setNextUrl(PaymentHelper::getCancelURL())
                    ->setMessage(__('Payment was not successful. Please try again.'));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Genie Payment Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->setMessage(__('An error occurred while processing your payment. Please contact support.'));
        }
    }

    /**
     * Handle webhook notifications from Genie Business
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $webhookData = $request->all();
            
            Log::info('Genie Webhook Received', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data' => $webhookData
            ]);

            // Process webhook through service
            $result = $this->geniePaymentService->processWebhook($request);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'timestamp' => now()->toISOString(),
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'timestamp' => now()->toISOString(),
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Genie Webhook Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Get transaction status via AJAX
     */
    public function getTransactionStatus(Request $request): JsonResponse
    {
        $transactionId = $request->input('transaction_id');
        
        if (!$transactionId) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required'
            ], 400);
        }

        try {
            $transaction = GenieTransaction::findByTransactionId($transactionId);
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Get fresh status from Genie API
            $paymentDetails = $this->geniePaymentService->getPaymentDetails($transactionId);
            
            if ($paymentDetails) {
                // Update local transaction status
                $apiStatus = $paymentDetails->state ?? 'UNKNOWN';
                $localStatus = $this->mapApiStatusToLocal($apiStatus);
                
                if ($transaction->status !== $localStatus) {
                    $transaction->update([
                        'status' => $localStatus,
                        'api_response' => (array)$paymentDetails,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'status_label' => $transaction->status_label,
                    'amount' => $transaction->formatted_amount,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'is_completed' => $transaction->isCompleted(),
                    'is_failed' => $transaction->isFailed(),
                    'is_pending' => $transaction->isPending(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting transaction status', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transaction status'
            ], 500);
        }
    }

    /**
     * Cancel payment
     */
    public function cancelPayment(Request $request): JsonResponse
    {
        $transactionId = $request->input('transaction_id');
        
        if (!$transactionId) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required'
            ], 400);
        }

        try {
            $transaction = GenieTransaction::findByTransactionId($transactionId);
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            if ($transaction->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel completed transaction'
                ], 400);
            }

            $transaction->update([
                'status' => GenieTransaction::STATUS_CANCELLED,
                'notes' => 'Cancelled by user at ' . now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling transaction', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling transaction'
            ], 500);
        }
    }

    /**
     * Admin endpoint to get transaction details
     */
    public function getTransactionDetails(Request $request): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->hasPermission('genie-payment.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $transactionId = $request->input('transaction_id');
        
        try {
            $transaction = GenieTransaction::with('customer')
                ->where('transaction_id', $transactionId)
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'charge_id' => $transaction->charge_id,
                    'order_id' => $transaction->order_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'status_label' => $transaction->status_label,
                    'payment_url' => $transaction->payment_url,
                    'short_url' => $transaction->short_url,
                    'customer_id' => $transaction->customer_id,
                    'customer_type' => $transaction->customer_type,
                    'expires_at' => $transaction->expires_at?->format('Y-m-d H:i:s'),
                    'verified_at' => $transaction->verified_at?->format('Y-m-d H:i:s'),
                    'webhook_received_at' => $transaction->webhook_received_at?->format('Y-m-d H:i:s'),
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $transaction->updated_at->format('Y-m-d H:i:s'),
                    'payment_data' => $transaction->payment_data,
                    'api_response' => $transaction->api_response,
                    'webhook_data' => $transaction->webhook_data,
                    'notes' => $transaction->notes,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting transaction details', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transaction details'
            ], 500);
        }
    }

    /**
     * Map Genie API status to local status
     */
    protected function mapApiStatusToLocal(string $apiStatus): string
    {
        return match (strtoupper($apiStatus)) {
            'INITIATED' => GenieTransaction::STATUS_INITIATED,
            'PENDING' => GenieTransaction::STATUS_PENDING,
            'CONFIRMED' => GenieTransaction::STATUS_CONFIRMED,
            'AUTHORIZED' => GenieTransaction::STATUS_AUTHORIZED,
            'FAILED' => GenieTransaction::STATUS_FAILED,
            'CANCELLED' => GenieTransaction::STATUS_CANCELLED,
            'EXPIRED' => GenieTransaction::STATUS_EXPIRED,
            default => strtolower($apiStatus),
        };
    }
}