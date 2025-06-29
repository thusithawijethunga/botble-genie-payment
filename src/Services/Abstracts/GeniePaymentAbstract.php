<?php

namespace Botble\GeniePayment\Services\Abstracts;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Exception;
use Botble\GeniePayment\Client\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class GeniePaymentAbstract
{
    use PaymentErrorTrait;

    protected GuzzleClient $httpClient;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $appId;
    protected string $paymentCurrency;
    protected float $totalAmount;
    protected string $returnUrl;
    protected string $cancelUrl;
    protected string $transactionDescription;
    protected string $customer;
    protected bool $debug;
    protected bool $supportRefundOnline;
    protected Client $genieClient;

    public function __construct()
    {
        $this->paymentCurrency = config('plugins.payment.payment.currency', 'LKR');
        $this->totalAmount = 0;
        $this->supportRefundOnline = false;
        $this->debug = setting('payment_genie_payment_debug', false);
        
        $this->setClient();
    }

    public function getSupportRefundOnline(): bool
    {
        return $this->supportRefundOnline;
    }

    protected function setClient(): self
    {
        $this->apiKey = setting('payment_genie_payment_app_key', '');
        $this->appId = setting('payment_genie_payment_app_id', '');
        $environment = setting('payment_genie_payment_environment', 'sandbox');

        $this->baseUrl = $environment === 'production'
            ? 'https://api.geniebiz.lk/public/'
            : 'https://api.uat.geniebiz.lk/public/';

        // Initialize Genie Business Client
        $this->genieClient = new Client($this->apiKey, $this->appId, $environment);

        $this->httpClient = new GuzzleClient([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $this->apiKey,
            ],
            'timeout' => 30,
        ]);

        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->paymentCurrency = $currency;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->paymentCurrency;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getCustomer(): string
    {
        return $this->customer;
    }

    public function setAmount(float $amount): self
    {
        $this->totalAmount = $amount;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setReturnUrl(string $url): self
    {
        $this->returnUrl = $url;
        return $this;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    public function setCancelUrl(string $url): self
    {
        $this->cancelUrl = $url;
        return $this;
    }

    public function getCancelUrl(): string
    {
        return $this->cancelUrl;
    }

    protected function preparePaymentPayload(array $data): array
    {
        $amount = (int)($this->totalAmount * 100); // Convert to cents

        $payload = [
            'currency' => $this->paymentCurrency,
            'amount' => $amount,
            'localId' => (string) $data['order_id'][0],
        ];

        if (!empty($this->returnUrl)) {
            $payload['redirectUrl'] = $this->returnUrl;
        }

        if (setting('payment_genie_payment_webhook_enabled', true)) {
            $payload['webhook'] = route('payments.genie.webhook');
        }

        $validityHours = setting('payment_genie_payment_validity_hours', 24);
        if ($validityHours > 0) {
            $payload['validForHours'] = (int)$validityHours;
            $payload['expires'] = now()->addHours($validityHours)->format('Y-m-d\TH:i:s.u\Z');
        }

        return $payload;
    }

    public function createPayment(string $transactionDescription, array $data): string|null|bool
    {
        $this->transactionDescription = $transactionDescription;

        try {
            $payload = $this->preparePaymentPayload($data);

            if ($this->debug) {
                Log::info('Genie Payment Request', ['payload' => $payload]);
            }

            $response = $this->genieClient->transactions->create($payload);

            if ($this->debug) {
                Log::info('Genie Payment Response', ['response' => $response]);
            }

            if (isset($response->url)) {
                // Store payment ID for later reference
                session(['genie_payment_id' => $response->id]);

                return $response->url;
            }

            throw new Exception('Invalid response from payment gateway');
        } catch (GuzzleException $e) {
            if ($this->debug) {
                Log::error('Genie Payment Error', ['error' => $e->getMessage()]);
            }
            $this->setErrorMessageAndLogging($e, 1);
            return false;
        } catch (Exception $e) {
            if ($this->debug) {
                Log::error('Genie Payment Error', ['error' => $e->getMessage()]);
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

        if (empty($transactionId) || empty($signature)) {
            return false;
        }

        // Validate signature
        if (!$this->validateSignature($signature, $this->totalAmount, $this->paymentCurrency)) {
            return false;
        }

        try {
            $response = $this->httpClient->get($this->baseUrl . "transactions/{$transactionId}");
            $transactionData = json_decode($response->getBody()->getContents(), true);

            if ($transactionData['state'] === 'CONFIRMED' || $transactionData['state'] === 'AUTHORIZED') {
                return 'COMPLETED';
            }

            return false;
        } catch (Exception $e) {
            if ($this->debug) {
                Log::error('Genie Payment Status Error', ['error' => $e->getMessage()]);
            }
            $this->setErrorMessageAndLogging($e, 1);
            return false;
        }
    }

    protected function validateSignature(string $signature, float $amount, string $currency): bool
    {
        $amountInCents = (int)($amount * 100);
        $expectedSignature = sha1("amount={$amountInCents}&currency={$currency}&apiKey={$this->apiKey}");
        return hash_equals($expectedSignature, $signature);
    }

    public function supportedCurrencyCodes(): array
    {
        return [
            'LKR', // Sri Lankan Rupee
            'USD', // US Dollar (for international transactions)
        ];
    }

    /**
     * Execute main service
     */
    abstract public function execute(array $data);

    /**
     * Make a payment
     */
    abstract public function makePayment(array $data);

    /**
     * Use this function to perform more logic after user has made a payment
     */
    abstract public function afterMakePayment(array $data);
}