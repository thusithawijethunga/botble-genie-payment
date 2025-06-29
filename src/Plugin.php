<?php

namespace Botble\GeniePayment;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        // Remove all plugin settings
        Setting::delete([
            'payment_genie_payment_name',
            'payment_genie_payment_description',
            'payment_genie_payment_app_id',
            'payment_genie_payment_app_key',
            'payment_genie_payment_environment',
            'payment_genie_payment_webhook_enabled',
            'payment_genie_payment_validity_hours',
            'payment_genie_payment_debug',
            'payment_genie_payment_status',
        ]);

        // Drop plugin tables if they exist
        Schema::dropIfExists('genie_payment_transactions');
        Schema::dropIfExists('genie_payment_logs');
    }

    public static function activate(): void
    {
        // Set default settings on activation
        $defaultSettings = [
            'payment_genie_payment_name' => 'Credit / Debit Card (Powered by Genie Business)',
            'payment_genie_payment_description' => 'Pay securely by Credit or Debit card through Genie Business payment gateway. We accept Visa, MasterCard, and American Express.',
            'payment_genie_payment_environment' => 'sandbox',
            'payment_genie_payment_webhook_enabled' => true,
            'payment_genie_payment_validity_hours' => 24,
            'payment_genie_payment_debug' => false,
            'payment_genie_payment_status' => false,
        ];

        foreach ($defaultSettings as $key => $value) {
            if (!Setting::has($key)) {
                Setting::set($key, $value);
            }
        }

        Setting::save();
    }

    public static function deactivate(): void
    {
        // Disable the payment method on deactivation
        Setting::set('payment_genie_payment_status', false);
        Setting::save();
    }

    public static function getInfo(): array
    {
        return [
            'name' => 'Genie Business Payment Gateway',
            'description' => 'Accept payments via Genie Business payment gateway. Supports Visa, MasterCard, and American Express cards.',
            'author' => 'Genie Business Team',
            'url' => 'https://geniebiz.lk',
            'version' => '1.0.0',
            'requirements' => [
                'botble/payment' => '^7.0',
            ],
            'features' => [
                'Secure SSL encrypted payments',
                'Support for Visa, MasterCard, and Amex',
                'Real-time webhook notifications', 
                'Sandbox and production environments',
                'Mobile-optimized payment pages',
                'Advanced fraud protection',
                'Easy integration and setup',
                'Transaction management dashboard',
                'Detailed payment logging',
                'Multi-currency support (LKR, USD)',
            ],
            'screenshots' => [
                'images/screenshot-1.png',
                'images/screenshot-2.png',
            ],
        ];
    }
}