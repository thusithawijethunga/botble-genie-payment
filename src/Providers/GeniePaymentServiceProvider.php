<?php

namespace Botble\GeniePayment\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class GeniePaymentServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (is_plugin_active('payment')) {
            $this->setNamespace('plugins/genie-payment')
                ->loadHelpers()
                ->loadRoutes()
                ->loadAndPublishViews()
                ->loadMigrations()
                ->loadAndPublishTranslations()
                ->publishAssets();

            $this->app->register(HookServiceProvider::class);
        }
    }

    public function register(): void
    {
        $this->app->singleton('genie-payment', function ($app) {
            return new \Botble\GeniePayment\Services\Gateways\GeniePaymentService();
        });
    }
}