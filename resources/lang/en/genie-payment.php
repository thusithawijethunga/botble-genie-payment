<?php

return [
    'name' => 'Genie Business Payment Gateway',
    'description' => 'Secure payments powered by Genie Business',
    'method_name' => 'Credit / Debit Card (Powered by Genie Business)',
    'method_description' => 'Pay securely by Credit or Debit card through Genie Business payment gateway. We accept Visa, MasterCard, and American Express.',
    'genie_description' => 'Customer can buy product and pay directly via Visa, Master, Amex cards',
    
    // Configuration
    'app_id' => 'Application ID',
    'app_key' => 'API Key',
    'app_key_placeholder' => 'Get your API Key from Genie Business Dashboard',
    'environment' => 'Environment',
    'sandbox' => 'Sandbox',
    'production' => 'Production',
    'validity_hours' => 'Payment Link Validity (Hours)',
    'validity_hours_help' => 'Payment links will expire after this many hours (1-2160 hours, default: 24)',
    'webhook_enabled' => 'Enable Webhooks',
    'webhook_help' => 'Enable webhooks to receive real-time payment updates',
    'debug' => 'Debug Mode',
    'debug_help' => 'Enable debug logging for troubleshooting',
    
    // Messages
    'sandbox_mode' => 'This payment method is currently in sandbox mode. No real transactions will be processed.',
    'please_provide_information' => 'Please provide information from',
    'service_registration' => 'Register for :name merchant account',
    'after_service_registration_msg' => 'After registration, get your API credentials from the Connect section',
    'enter_app_credentials' => 'Enter your Application ID and API Key',
    
    // Payment method details
    'accepted_cards' => 'Accepted Cards',
    'secure_payment' => 'Secure Payment',
    'encrypted' => 'Encrypted',
    'instant_processing' => 'Instant Processing',
    'no_hidden_fees' => 'No Hidden Fees',
    'card_payment' => 'Card Payment',
    
    // Payment details
    'payment_details' => 'Payment Details',
    'payment_method' => 'Payment Method',
    'merchant_id' => 'Merchant ID',
    'payment_links' => 'Payment Links',
    'payment_url' => 'Payment URL',
    'short_url' => 'Short URL',
    'order_id' => 'Order Id',
    'updated_at' => 'Updated at',
    'expires_at' => 'Expires at',
    'secured_by_genie' => 'This transaction is secured by Genie Business',
    'no_payment_details' => 'Payment details are not available.',
    
    // Status translations
    'status_initiated' => 'Payment Initiated',
    'status_confirmed' => 'Payment Confirmed',
    'status_authorized' => 'Payment Authorized',
    'status_failed' => 'Payment Failed',
    'status_cancelled' => 'Payment Cancelled',
    'status_expired' => 'Payment Expired',
    
    // Error messages
    'currency_not_supported' => ':name doesn\'t support :currency. Supported currencies: :currencies',
    'payment_failed' => 'Payment processing failed. Please try again.',
    'invalid_amount' => 'Invalid payment amount.',
    'transaction_not_found' => 'Transaction not found.',
    'invalid_signature' => 'Invalid payment signature.',
    
    // Success messages
    'payment_successful' => 'Payment completed successfully!',
    'webhook_processed' => 'Payment notification processed successfully.',
    
    // Instructions
    'test_instructions' => 'Test Instructions',
    'test_cards_title' => 'Test Cards for Sandbox',
    'test_cards_note' => 'These are dummy cards for testing purposes only. No real transactions will be processed.',
    'sandbox_warning' => 'You are in sandbox mode. Use test card numbers for transactions.',
    
    // Test card information
    'mastercard_test_cards' => 'MasterCard Test Cards',
    'visa_test_cards' => 'Visa Test Cards',
    'card_number' => 'Card Number',
    'expiry_date' => 'Expiry Date',
    'cvv_code' => 'CVV Code',
    
    // API information
    'api_documentation' => 'API Documentation',
    'api_docs_url' => 'https://geniebusiness.stoplight.io/',
    'dashboard_url' => 'https://dashboard.geniebiz.lk',
    'support_email' => 'genie.integration@dialog.lk',
    'support_phone' => '+94 777 337 927',
    
    // Setup instructions
    'setup_steps' => [
        'step_1' => 'Create a Genie Business merchant account',
        'step_2' => 'Get your API credentials from the dashboard',
        'step_3' => 'Configure the plugin with your credentials',
        'step_4' => 'Test with sandbox mode first',
        'step_5' => 'Switch to production when ready',
    ],
    
    // Features
    'features' => [
        'secure_payments' => 'Secure SSL encrypted payments',
        'multiple_cards' => 'Accept Visa, MasterCard, and Amex',
        'real_time_notifications' => 'Real-time payment notifications',
        'easy_integration' => 'Easy integration with your website',
        'fraud_protection' => 'Advanced fraud protection',
        'mobile_optimized' => 'Mobile-optimized payment pages',
    ],
];