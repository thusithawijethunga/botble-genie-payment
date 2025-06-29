@if (setting('payment_genie_payment_status') == 1)
    <li class="list-group-item">
        <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_genie_payment"
               value="genie_payment"
               @if ($selecting == GENIE_PAYMENT_METHOD_NAME) checked @endif>
        <label for="payment_genie_payment" class="text-start">
            <span class="d-inline-flex align-items-center">
                <img src="{{ url('vendor/core/plugins/genie-payment/images/genie-logo.png') }}" 
                     alt="Genie Business" style="height: 30px; margin-right: 10px;">
                {{ setting('payment_genie_payment_name', trans('plugins/genie-payment::genie-payment.method_name')) }}
            </span>
        </label>
        <div class="payment_genie_payment_wrap payment_collapse_wrap collapse @if ($selecting == GENIE_PAYMENT_METHOD_NAME) show @endif" style="padding: 15px 0;">
            <p>{!! BaseHelper::clean(setting('payment_genie_payment_description', trans('plugins/genie-payment::genie-payment.method_description'))) !!}</p>

            @php 
                $supportedCurrencies = (new \Botble\GeniePayment\Services\Gateways\GeniePaymentService)->supportedCurrencyCodes(); 
                $currentCurrency = function_exists('get_application_currency') ? get_application_currency() : null;
            @endphp
            
            @if ($currentCurrency && !in_array($currentCurrency->title, $supportedCurrencies))
                <div class="alert alert-warning" style="margin-top: 15px;">
                    <i class="fa fa-exclamation-triangle"></i>
                    {{ __(":name doesn't support :currency. List of currencies supported by :name: :currencies.", [
                        'name' => 'Genie Business', 
                        'currency' => $currentCurrency->title, 
                        'currencies' => implode(', ', $supportedCurrencies)
                    ]) }}

                    <div style="margin-top: 10px;">
                        {{ __('Learn more') }}: <a href="https://geniebiz.lk" target="_blank" rel="nofollow">https://geniebiz.lk</a>
                    </div>

                    @php
                        $currencies = function_exists('get_all_currencies') ? get_all_currencies() : collect();
                        $currencies = $currencies->filter(function ($item) use ($supportedCurrencies) { 
                            return in_array($item->title, $supportedCurrencies); 
                        });
                    @endphp
                    
                    @if ($currencies->count() > 0)
                        <div style="margin-top: 10px;">
                            {{ __('Please switch currency to any supported currency') }}:&nbsp;&nbsp;
                            @foreach ($currencies as $currency)
                                <a href="{{ route('public.change-currency', $currency->title) }}" 
                                   @if (function_exists('get_application_currency_id') && get_application_currency_id() == $currency->id) class="active" @endif>
                                    <span>{{ $currency->title }}</span>
                                </a>
                                @if (!$loop->last)
                                    &nbsp; | &nbsp;
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if (setting('payment_genie_payment_environment', 'sandbox') === 'sandbox')
                <div class="alert alert-info" style="margin-top: 15px;">
                    <i class="fa fa-info-circle"></i>
                    {{ trans('plugins/genie-payment::genie-payment.sandbox_mode') }}
                </div>
            @endif

            <div class="payment-methods-info mt-3">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="mb-2">{{ trans('plugins/genie-payment::genie-payment.accepted_cards') }}</h6>
                        <div class="d-flex align-items-center">
                            <i class="fab fa-cc-visa text-primary me-2" style="font-size: 2rem;"></i>
                            <i class="fab fa-cc-mastercard text-warning me-2" style="font-size: 2rem;"></i>
                            <i class="fab fa-cc-amex text-info me-2" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="security-badges text-end">
                            <div class="mb-1">
                                <i class="fa fa-shield-alt text-success"></i>
                                <small class="text-muted">{{ trans('plugins/genie-payment::genie-payment.secure_payment') }}</small>
                            </div>
                            <div>
                                <i class="fa fa-lock text-success"></i>
                                <small class="text-muted">SSL {{ trans('plugins/genie-payment::genie-payment.encrypted') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="payment-features mt-3">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">
                            <i class="fa fa-check text-success"></i>
                            {{ trans('plugins/genie-payment::genie-payment.instant_processing') }}
                        </small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">
                            <i class="fa fa-check text-success"></i>
                            {{ trans('plugins/genie-payment::genie-payment.no_hidden_fees') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </li>
@endif