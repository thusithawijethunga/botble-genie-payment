@if ($payment)
    @php
        $result = $payment;
    @endphp
    <div class="alert alert-success" role="alert">
        <div class="d-flex align-items-center mb-3">
            <img src="{{ url('vendor/core/plugins/genie-payment/images/genie-logo.png') }}" 
                 alt="Genie Business" style="height: 40px; margin-right: 15px;">
            <h5 class="mb-0">{{ trans('plugins/genie-payment::genie-payment.payment_details') }}</h5>
        </div>

        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>{{ trans('plugins/payment::payment.payment_id') }}:</strong> 
                    <code>{{ $result->id ?? 'N/A' }}</code>
                </p>

                <p class="mb-2">
                    <strong>{{ trans('plugins/payment::payment.amount') }}:</strong>
                    <span class="badge badge-success">
                        {{ $result->amountFormatted ?? ($result->amount->value ?? 'N/A') . ' ' . ($result->currency ?? 'LKR') }}
                    </span>
                </p>

                @if (isset($result->localId))
                    <p class="mb-2">
                        <strong>{{ trans('plugins/genie-payment::genie-payment.order_id') }}:</strong> 
                        {{ $result->localId }}
                    </p>
                @endif

                @if (isset($result->state))
                    <p class="mb-2">
                        <strong>{{ trans('plugins/payment::payment.status') }}:</strong> 
                        <span class="badge badge-{{ $result->state === 'CONFIRMED' ? 'success' : ($result->state === 'FAILED' ? 'danger' : 'warning') }}">
                            {{ trans('plugins/genie-payment::genie-payment.status_' . strtolower($result->state)) }}
                        </span>
                    </p>
                @endif
            </div>
            
            <div class="col-md-6">
                @if (isset($result->created))
                    <p class="mb-2">
                        <strong>{{ trans('plugins/payment::payment.created_at') }}:</strong> 
                        {{ \Carbon\Carbon::parse($result->created)->format('d M Y, h:i A') }}
                    </p>
                @endif

                @if (isset($result->updated))
                    <p class="mb-2">
                        <strong>{{ trans('plugins/genie-payment::genie-payment.updated_at') }}:</strong> 
                        {{ \Carbon\Carbon::parse($result->updated)->format('d M Y, h:i A') }}
                    </p>
                @endif

                @if (isset($result->provider))
                    <p class="mb-2">
                        <strong>{{ trans('plugins/genie-payment::genie-payment.payment_method') }}:</strong> 
                        {{ ucfirst($result->provider) ?? trans('plugins/genie-payment::genie-payment.card_payment') }}
                    </p>
                @endif

                @if (isset($result->merchantId))
                    <p class="mb-2">
                        <strong>{{ trans('plugins/genie-payment::genie-payment.merchant_id') }}:</strong> 
                        <small><code>{{ Str::mask($result->merchantId, '*', 4, -4) }}</code></small>
                    </p>
                @endif
            </div>
        </div>

        @if (isset($result->url) || isset($result->shortUrl))
            <hr class="my-3">
            <div class="payment-links">
                <h6>{{ trans('plugins/genie-payment::genie-payment.payment_links') }}</h6>
                
                @if (isset($result->url))
                    <p class="mb-1">
                        <strong>{{ trans('plugins/genie-payment::genie-payment.payment_url') }}:</strong>
                        <a href="{{ $result->url }}" target="_blank" class="text-primary">
                            {{ Str::limit($result->url, 60) }}
                            <i class="fa fa-external-link-alt ml-1"></i>
                        </a>
                    </p>
                @endif

                @if (isset($result->shortUrl))
                    <p class="mb-1">
                        <strong>{{ trans('plugins/genie-payment::genie-payment.short_url') }}:</strong>
                        <a href="{{ $result->shortUrl }}" target="_blank" class="text-primary">
                            {{ $result->shortUrl }}
                            <i class="fa fa-external-link-alt ml-1"></i>
                        </a>
                    </p>
                @endif
            </div>
        @endif

        @if (isset($result->expires))
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fa fa-clock"></i>
                    {{ trans('plugins/genie-payment::genie-payment.expires_at') }}: 
                    {{ \Carbon\Carbon::parse($result->expires)->format('d M Y, h:i A') }}
                </small>
            </div>
        @endif

        <div class="mt-3">
            <small class="text-muted">
                <i class="fa fa-shield-alt text-success"></i>
                {{ trans('plugins/genie-payment::genie-payment.secured_by_genie') }}
            </small>
        </div>
    </div>

    @include('plugins/payment::partials.view-payment-source')
@else
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        {{ trans('plugins/genie-payment::genie-payment.no_payment_details') }}
    </div>
@endif