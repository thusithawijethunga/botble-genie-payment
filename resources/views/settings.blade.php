@php $genieStatus = setting('payment_genie_payment_status'); @endphp
<table class="table payment-method-item">
    <tbody>
    <tr class="border-pay-row">
        <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
        <td style="width: 20%;">
            <img class="filter-black" src="{{ url('vendor/core/plugins/genie-payment/images/genie.png') }}" alt="Genie Business">
        </td>
        <td class="border-right">
            <ul>
                <li>
                    <a href="https://geniebiz.lk" target="_blank">Genie Business Payment Gateway</a>
                    <p>{{ trans('plugins/genie-payment::genie-payment.genie_description') }}</p>
                </li>
            </ul>
        </td>
    </tr>
    <tr class="bg-white">
        <td colspan="3">
            <div class="float-start" style="margin-top: 5px;">
                <div class="payment-name-label-group  @if ($genieStatus== 0) hidden @endif">
                    <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span> 
                    <label class="ws-nm inline-display method-name-label">{{ setting('payment_genie_payment_name') }}</label>
                </div>
            </div>
            <div class="float-end">
                <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($genieStatus == 0) hidden @endif">
                    {{ trans('plugins/payment::payment.edit') }}
                </a>
                <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($genieStatus == 1) hidden @endif">
                    {{ trans('plugins/payment::payment.settings') }}
                </a>
            </div>
        </td>
    </tr>
    <tr class="payment-content-item hidden">
        <td class="border-left" colspan="3">
            {!! Form::open() !!}
            {!! Form::hidden('type', GENIE_PAYMENT_METHOD_NAME, ['class' => 'payment_type']) !!}
            <div class="row">
                <div class="col-sm-6">
                    <ul>
                        <li>
                            <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => 'Genie Business']) }}</label>
                        </li>
                        <li class="payment-note">
                            <p>{{ trans('plugins/payment::payment.configuration_requirement', ['name' => 'Genie Business']) }}:</p>
                            <ul class="m-md-l" style="list-style-type:decimal">
                                <li style="list-style-type:decimal">
                                    <a href="https://dashboard.geniebiz.lk" target="_blank">
                                        {{ trans('plugins/genie-payment::genie-payment.service_registration', ['name' => 'Genie Business']) }}
                                    </a>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ trans('plugins/genie-payment::genie-payment.after_service_registration_msg', ['name' => 'Genie Business']) }}</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ trans('plugins/genie-payment::genie-payment.enter_app_credentials') }}</p>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <div class="well bg-white">
                        <x-core-setting::text-input
                            name="payment_genie_payment_name"
                            :label="trans('plugins/payment::payment.method_name')"
                            :value="setting('payment_genie_payment_name', trans('plugins/genie-payment::genie-payment.method_name'))"
                            data-counter="400"
                        />

                        <div class="form-group mb-3">
                            <label class="text-title-field" for="payment_genie_payment_description">{{ trans('core/base::forms.description') }}</label>
                            <textarea class="next-input" name="payment_genie_payment_description" id="payment_genie_payment_description">{{ setting('payment_genie_payment_description', trans('plugins/genie-payment::genie-payment.method_description')) }}</textarea>
                        </div>

                        <p class="payment-note">
                            {{ trans('plugins/genie-payment::genie-payment.please_provide_information') }} <a target="_blank" href="https://dashboard.geniebiz.lk">Genie Business</a>:
                        </p>

                        <x-core-setting::text-input
                            name="payment_genie_payment_app_id"
                            :label="trans('plugins/genie-payment::genie-payment.app_id')"
                            :value="BaseHelper::hasDemoModeEnabled() ? '*******************************' : setting('payment_genie_payment_app_id')"
                            placeholder="36bafce7-a201-429b-a9e2-c5b78546677c"
                        />

                        <div class="form-group mb-3">
                            <label class="text-title-field" for="payment_genie_payment_app_key">{{ trans('plugins/genie-payment::genie-payment.app_key') }}</label>
                            <textarea class="next-input" name="payment_genie_payment_app_key" id="payment_genie_payment_app_key" rows="3" placeholder="{{ trans('plugins/genie-payment::genie-payment.app_key_placeholder') }}">{{ BaseHelper::hasDemoModeEnabled() ? '*******************************' : setting('payment_genie_payment_app_key') }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="text-title-field" for="payment_genie_payment_environment">{{ trans('plugins/genie-payment::genie-payment.environment') }}</label>
                            <select class="next-input" name="payment_genie_payment_environment" id="payment_genie_payment_environment">
                                <option value="sandbox" @if (setting('payment_genie_payment_environment', 'sandbox') == 'sandbox') selected @endif>{{ trans('plugins/genie-payment::genie-payment.sandbox') }}</option>
                                <option value="production" @if (setting('payment_genie_payment_environment', 'sandbox') == 'production') selected @endif>{{ trans('plugins/genie-payment::genie-payment.production') }}</option>
                            </select>
                        </div>

                        <x-core-setting::text-input
                            name="payment_genie_payment_validity_hours"
                            type="number"
                            :label="trans('plugins/genie-payment::genie-payment.validity_hours')"
                            :value="setting('payment_genie_payment_validity_hours', 24)"
                            :helper="trans('plugins/genie-payment::genie-payment.validity_hours_help')"
                            min="1"
                            max="2160"
                        />

                        <x-core-setting::checkbox
                            name="payment_genie_payment_webhook_enabled"
                            :label="trans('plugins/genie-payment::genie-payment.webhook_enabled')"
                            :checked="setting('payment_genie_payment_webhook_enabled', true)"
                            :helper="trans('plugins/genie-payment::genie-payment.webhook_help')"
                        />

                        <x-core-setting::checkbox
                            name="payment_genie_payment_debug"
                            :label="trans('plugins/genie-payment::genie-payment.debug')"
                            :checked="setting('payment_genie_payment_debug', false)"
                            :helper="trans('plugins/genie-payment::genie-payment.debug_help')"
                        />

                        {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, 'genie_payment') !!}
                    </div>
                </div>
            </div>
            <div class="col-12 bg-white text-end">
                <button class="btn btn-warning disable-payment-item @if ($genieStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                <button class="btn btn-info save-payment-item btn-text-trigger-save @if ($genieStatus == 1) hidden @endif" type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                <button class="btn btn-info save-payment-item btn-text-trigger-update @if ($genieStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.update') }}</button>
            </div>
            {!! Form::close() !!}
        </td>
    </tr>
    </tbody>
</table>