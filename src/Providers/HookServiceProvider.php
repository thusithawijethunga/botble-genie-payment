<?php

namespace Botble\GeniePayment\Providers;

use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\GeniePayment\Services\Gateways\GeniePaymentService;
use Botble\Base\Facades\Html;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Botble\Payment\Facades\PaymentMethods;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerGenieMethod'], 16, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithGenie'], 16, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 16);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['GENIE_PAYMENT'] = GENIE_PAYMENT_METHOD_NAME;
            }
            return $values;
        }, 16, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == GENIE_PAYMENT_METHOD_NAME) {
                $value = 'Genie Business Payment Gateway';
            }
            return $value;
        }, 16, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == GENIE_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )->toHtml();
            }
            return $value;
        }, 16, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == GENIE_PAYMENT_METHOD_NAME) {
                $data = GeniePaymentService::class;
            }
            return $data;
        }, 16, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == GENIE_PAYMENT_METHOD_NAME) {
                $paymentDetail = (new GeniePaymentService())->getPaymentDetails($payment->charge_id);
                $data = view('plugins/genie-payment::detail', ['payment' => $paymentDetail])->render();
            }
            return $data;
        }, 16, 2);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . view('plugins/genie-payment::settings')->render();
    }

    public function registerGenieMethod(?string $html, array $data): string
    {
        PaymentMethods::method(GENIE_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/genie-payment::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithGenie(array $data, Request $request): array
    {
        if ($request->input('payment_method') == GENIE_PAYMENT_METHOD_NAME) {
            $currentCurrency = get_application_currency();
            $genieService = $this->app->make(GeniePaymentService::class);
            $supportedCurrencies = $genieService->supportedCurrencyCodes();
            $currency = strtoupper($currentCurrency->title);

            // Check currency support
            if (!in_array($currency, $supportedCurrencies)) {
                $data['error'] = true;
                $data['message'] = __(":name doesn't support :currency. List of currencies supported by :name: :currencies.", [
                    'name' => 'Genie Business',
                    'currency' => $currency,
                    'currencies' => implode(', ', $supportedCurrencies),
                ]);
                return $data;
            }

            $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

            if (!$request->input('callback_url')) {
                $paymentData['callback_url'] = route('payments.genie.status');
            }

            $checkoutUrl = $genieService->execute($paymentData);

            if ($checkoutUrl) {
                $data['checkoutUrl'] = $checkoutUrl;
            } else {
                $data['error'] = true;
                $data['message'] = $genieService->getErrorMessage();
            }

            return $data;
        }

        return $data;
    }
}