<?php

namespace Botble\GeniePayment\Services\Gateways;

use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Botble\GeniePayment\Services\Abstracts\GeniePaymentAbstract;
use Botble\GeniePayment\Client\Client;
use Botble\GeniePayment\Models\GenieTransaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class GeniePaymentService extends GeniePaymentAbstract
{
    public function makePayment(array $data)
    {
        $amount = round((float)$data['amount'], 2);
        $currency = strtoupper($data['currency']);

        $this->setAmount($amount);
        $this->setCurrency($currency);

        $queryParams = [
            'type' => GENIE_PAYMENT_METHOD_NAME,
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $data['order_id'],
            'customer_id' => Arr::get($data, 'customer_id'),
            'customer_type' => Arr::get($data, 'customer_type'),
        ];

        if ($cancelUrl = $data['return_url'] ?: PaymentHelper::getCancelURL()) {
            $this->setCancelUrl($cancelUrl);
        }

        $description = Str::limit($data['description'], 50);

        return $this
            ->setReturnUrl($data['callback_url'] . '?' . http_build_query($queryParams))
            ->setCustomer(Arr::get($data, 'address.email', ''))
            ->createPayment($description, $data);
    }

    public function afterMakePayment(array $data): string|null
    {
        $status = PaymentStatusEnum::COMPLETED;
        $chargeId = session('genie_payment_id');
        $orderIds = (array)Arr::get($data, 'order_id', []);

        // Store transaction details in our custom table
        $this->storeTransaction($data, $chargeId, $status);

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'charge_id' => $chargeId,
            'order_id' => $orderIds,
            'customer_id' => Arr::get($data, 'customer_id'),
            'customer_type' => Arr::get($data, 'customer_type'),
            'payment_channel' => GENIE_PAYMENT_METHOD_NAME,
            'status' => $status,
        ]);

        session()->forget('genie_payment_id');

        return $chargeId;
    }

    public function execute(array $data)
    {
        try {
            return $this->makePayment($data);
        } catch (\Exception $exception) {
            if ($this->debug) {
                Log::error('Genie Payment Execute Error', [
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                    'data' => $data
                ]);
            }
            $this->setErrorMessageAndLogging($exception, 1);
            return false;
        }
    }

    public function getPaymentDetails(string $paymentId)
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . "transactions/{$paymentId}");
            $data = json_decode($response->getBody()->getContents());
            
            if ($this->debug) {
                Log::info('Genie Payment Details Retrieved', ['payment_id' => $paymentId, 'data' => $data]);
            }
            
            return $data;
        } catch (\Exception $e) {
            if ($this->debug) {
                Log::error('Genie Payment Details Error', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage()
                ]);
            }
            $this->setErrorMessageAndLogging($e, 1);
            return false;
        }
    }

    public function getPaymentStatus(Request $request)
    {
        $transactionId = $request->input('transactionId');
        $signature = $request->input('signature');
        $state = $request->input('state');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        if (empty($transactionId)) {
            if ($this->debug) {
                Log::warning('Genie Payment Status Check - Missing transaction ID');
            }
            return false;
        }

        try {
            // Get transaction details from Genie API
            $transactionData = $this->getPaymentDetails($transactionId);
            
            if (!$transactionData) {
                return false;
            }

            // Validate signature if provided
            if ($signature && !$this->validateSignature($signature, $amount, $currency)) {
                if ($this->debug) {
                    Log::warning('Genie Payment Status Check - Invalid signature', [
                        'transaction_id' => $transactionId,
                        'signature' => $signature
                    ]);
                }
                return false;
            }

            // Check if payment is confirmed
            if (in_array($transactionData->state ?? '', ['CONFIRMED', 'AUTHORIZED'])) {
                return 'COMPLETED';
            }

            if (in_array($transactionData->state ?? '', ['FAILED', 'CANCELLED'])) {
                return 'FAILED';
            }

            return 'PENDING';
        } catch (\Exception $e) {
            if ($this->debug) {
                Log::error('Genie Payment Status Error', [
                    'transaction_id' => $transactionId,
                    'error' => $e->getMessage()
                ]);
            }
            $this->setErrorMessageAndLogging($e, 1);
            return false;
        }
    }

    protected function storeTransaction(array $data, ?string $chargeId, $status): void
    {
        try {
            $transactionId = session('genie_transaction_id');
            $orderIds = (array)Arr::get($data, 'order_id', []);
            $orderId = is_array($orderIds) ? implode(',', $orderIds) : $orderIds;

            GenieTransaction::updateOrCreate(
                [
                    'transaction_id' => $transactionId,
                    'order_id' => $orderId,
                ],
                [
                    'charge_id' => $chargeId,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'status' => strtolower($status),
                    'customer_id' => Arr::get($data, 'customer_id'),
                    'customer_type' => Arr::get($data, 'customer_type'),
                    'payment_data' => json_encode($data),
                    'verified_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            if ($this->debug) {
                Log::error('Failed to store Genie transaction', [
                    'error' => $e->getMessage(),
                    'data' => $data
                ]);
            }
        }
    }

    public function refundPayment(string $paymentId, float $amount, array $options = []): bool
    {
        // Genie Business doesn't support automated refunds via API
        // This would need to be handled manually through their dashboard
        if ($this->debug) {
            Log::info('Genie Payment Refund Requested (Manual Process Required)', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'options' => $options
            ]);
        }
        
        return false;
    }

    public function supportedCurrencyCodes(): array
    {
        return [
            'LKR', // Sri Lankan Rupee (primary)
            'USD', // US Dollar (for international transactions)
        ];
    }

    public function isSandbox(): bool
    {
        return setting('payment_genie_payment_environment', 'sandbox') === 'sandbox';
    }

    public function getWebhookUrl(): string
    {
        return route('payments.genie.webhook');
    }

    public function processWebhook(Request $request): array
    {
        try {
            $webhookData = $request->all();
            
            if ($this->debug) {
                Log::info('Genie Webhook Received', ['data' => $webhookData]);
            }

            $transactionId = $webhookData['transactionId'] ?? null;
            $signature = $webhookData['signature'] ?? null;
            $state = $webhookData['state'] ?? null;

            if (!$transactionId || !$signature) {
                return [
                    'success' => false,
                    'message' => 'Missing required webhook parameters',
                ];
            }

            // Update transaction status based on webhook
            if ($transaction = GenieTransaction::where('transaction_id', $transactionId)->first()) {
                $transaction->update([
                    'status' => strtolower($state),
                    'webhook_data' => json_encode($webhookData),
                    'webhook_received_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
            ];
        } catch (\Exception $e) {
            if ($this->debug) {
                Log::error('Genie Webhook Processing Error', ['error' => $e->getMessage()]);
            }
            
            return [
                'success' => false,
                'message' => 'Internal server error',
            ];
        }
    }
}