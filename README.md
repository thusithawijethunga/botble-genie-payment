# Genie Business Payment Gateway for Botble CMS

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/thusithawijethunga/botble-genie-payment)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Botble](https://img.shields.io/badge/Botble-7.0+-orange.svg)](https://botble.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)

A comprehensive payment gateway plugin for Botble CMS that integrates with Genie Business (Dialog Axiata PLC) to accept secure online payments in Sri Lanka. This plugin enables merchants to accept Visa, MasterCard, and American Express payments through Genie Business's robust payment infrastructure.

## 🌟 Features

### 💳 Payment Processing
- **Secure Payment Processing**: SSL-encrypted transactions with PCI compliance
- **Multiple Card Support**: Accept Visa, MasterCard, and American Express
- **Multi-Currency**: Support for LKR (primary) and USD
- **Mobile Optimized**: Responsive payment pages for all devices
- **Real-time Processing**: Instant payment confirmation and processing

### 🔧 Technical Features
- **Webhook Integration**: Real-time payment status updates
- **Signature Validation**: Secure API signature verification
- **Transaction Management**: Complete transaction lifecycle tracking
- **Debug Mode**: Comprehensive logging for development and troubleshooting
- **Sandbox Support**: Full testing environment integration
- **Database Logging**: Detailed transaction and webhook logging

### 🎯 Business Features
- **Package Subscriptions**: Seamless integration with Botble's job board packages
- **Customer Management**: Track payments by customer and order
- **Payment Analytics**: Transaction reporting and status monitoring
- **Refund Support**: Manual refund process through Genie dashboard
- **Multi-language**: English language support with easy translation

### 🛡️ Security & Compliance
- **API Authentication**: Secure API key and application ID authentication
- **Request Signing**: SHA1 signature validation for all transactions
- **Environment Separation**: Dedicated sandbox and production environments
- **Error Handling**: Comprehensive error handling and user feedback
- **CSRF Protection**: Built-in CSRF token validation

## 📋 Requirements

- **Botble CMS**: Version 6.4 or higher
- **PHP**: Version 8.1 or higher
- **Extensions**: `php-curl`, `php-json`, `php-mbstring`
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **SSL Certificate**: Required for production environment
- **Genie Business Account**: Active merchant account with Dialog Axiata

## 🚀 Installation

### Method 1: Via Admin Panel (Recommended)

1. **Download the Plugin**
   ```bash
   git clone https://github.com/thusithawijethunga/botble-genie-payment.git
   zip -r botble-genie-payment.zip genie-payment/
   ```

2. **Upload via Admin Panel**
   - Navigate to `Admin Panel > Plugins > Add New`
   - Click "Upload Plugin" and select `genie-payment.zip`
   - Click "Install Now"

3. **Activate the Plugin**
   - Go to `Admin Panel > Plugins`
   - Find "Genie Business Payment Gateway"
   - Click "Activate"

### Method 2: Manual Installation

1. **Download and Extract**
   ```bash
   cd platform/plugins/
   git clone https://github.com/thusithawijethunga/botble-genie-payment.git
   cd genie-payment
   ```

2. **Install Dependencies**
   ```bash
   composer install --no-dev
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate
   ```

4. **Activate via Admin Panel**
   - Navigate to `Admin Panel > Plugins`
   - Find and activate "Genie Business Payment Gateway"

## ⚙️ Configuration

### 1. Get API Credentials

1. **Login to Genie Business Dashboard**
   - Visit: [https://dashboard.geniebiz.lk](https://dashboard.geniebiz.lk)
   - Use your registered mobile number to login

2. **Get API Keys**
   - Navigate to `Connect` section
   - Copy your `Application ID` and `App Key`

### 2. Configure Plugin Settings

1. **Access Payment Settings**
   - Go to `Admin Panel > Payments > Payment Methods`
   - Find "Genie Business Payment Gateway"
   - Click "Settings"

2. **Enter Configuration**
   ```
   Method Name: Credit / Debit Card (Powered by Genie Business)
   Description: Pay securely by Credit or Debit card through Genie Business...
   Application ID: [Your Application ID from Genie Dashboard]
   API Key: [Your App Key from Genie Dashboard]
   Environment: Sandbox (for testing) / Production (for live)
   Payment Link Validity: 24 hours (1-2160 hours)
   Enable Webhooks: Yes (recommended)
   Debug Mode: Yes (for development)
   ```

3. **Save and Activate**
   - Click "Activate" to enable the payment method

### 3. Test Configuration

1. **Use Test Cards** (Sandbox Mode)
   ```
   Visa: 4761 3400 0000 0035 | 12/25 | 817
   MasterCard: 5099 9930 0011 6951 | 03/27 | 986
   ```

2. **Verify Webhook URL**
   - Ensure `https://yourdomain.com/payment/genie/webhook` is accessible
   - Check firewall and server configurations

## 📖 Usage

### For Customers

1. **Select Payment Method**
   - During checkout, select "Credit / Debit Card (Powered by Genie Business)"
   - Review payment details and terms

2. **Complete Payment**
   - Click "Pay Now" to redirect to Genie Business payment page
   - Enter card details and complete 3D Secure authentication
   - Automatic redirect back to your site upon completion

### For Merchants

1. **Monitor Transactions**
   - View transaction status in `Admin Panel > Payments`
   - Real-time status updates via webhooks
   - Detailed transaction logs and analytics

2. **Handle Refunds**
   - Refunds must be processed manually through Genie Business dashboard
   - Update order status in Botble admin panel accordingly

## 🔗 API Integration

### Transaction Creation

```php
use Botble\GeniePayment\Services\Gateways\GeniePaymentService;

$genieService = new GeniePaymentService();

$paymentData = [
    'amount' => 250.00,
    'currency' => 'LKR',
    'order_id' => [123],
    'description' => 'Package Subscription Payment',
    'customer_id' => 456,
    'customer_type' => 'App\\Models\\Customer',
    'callback_url' => route('payment.callback'),
    'return_url' => route('payment.return'),
    'address' => [
        'email' => 'customer@example.com',
        'name' => 'John Doe'
    ]
];

$paymentUrl = $genieService->execute($paymentData);
```

### Status Checking

```php
use Botble\GeniePayment\Models\GenieTransaction;

// Check transaction status
$transaction = GenieTransaction::findByTransactionId($transactionId);

if ($transaction->isCompleted()) {
    // Payment successful
} elseif ($transaction->isFailed()) {
    // Payment failed
} else {
    // Payment pending
}
```

### Webhook Handling

```php
// Webhook endpoint: POST /payment/genie/webhook
// Automatically handled by GeniePaymentController@webhook

// Manual webhook processing
$webhookData = $request->all();
$result = $genieService->processWebhook($request);
```

## 🧪 Testing

### Unit Tests

```bash
# Run plugin tests
php artisan test --filter=GeniePayment

# Run with coverage
php artisan test --filter=GeniePayment --coverage
```

### Integration Testing

1. **Sandbox Environment**
   - Set environment to "Sandbox"
   - Use provided test card numbers
   - Verify webhook delivery

2. **Payment Flow Testing**
   ```bash
   # Test payment creation
   curl -X POST https://yourdomain.com/test-payment \
        -H "Content-Type: application/json" \
        -d '{"amount": 100, "currency": "LKR"}'
   ```

### Test Cases

- ✅ Payment method selection
- ✅ Payment URL generation
- ✅ Successful payment processing
- ✅ Failed payment handling
- ✅ Webhook reception and processing
- ✅ Transaction status updates
- ✅ Signature validation
- ✅ Error handling and logging

## 🔧 Troubleshooting

### Common Issues

1. **Payment URL Not Generated**
   ```
   Issue: "Invalid response from payment gateway"
   Solution: Check API credentials and network connectivity
   ```

2. **Webhook Not Received**
   ```
   Issue: Payment status not updating automatically
   Solution: Verify webhook URL accessibility and firewall settings
   ```

3. **Signature Validation Failed**
   ```
   Issue: "Invalid payment signature"
   Solution: Ensure API key is correct and not truncated
   ```

### Debug Mode

Enable debug mode to get detailed logs:

```php
// In plugin settings
'payment_genie_payment_debug' => true

// Check logs
tail -f storage/logs/laravel.log | grep "Genie"
```

### Log Files

```bash
# Application logs
storage/logs/laravel.log

# Web server logs
/var/log/nginx/access.log
/var/log/nginx/error.log
```

## 📊 Database Schema

### Transactions Table

```sql
CREATE TABLE `genie_payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) DEFAULT NULL,
  `charge_id` varchar(255) DEFAULT NULL,
  `order_id` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'LKR',
  `status` varchar(255) NOT NULL DEFAULT 'initiated',
  `payment_url` text,
  `short_url` text,
  `customer_id` varchar(255) DEFAULT NULL,
  `customer_type` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `webhook_received_at` timestamp NULL DEFAULT NULL,
  `payment_data` json DEFAULT NULL,
  `api_response` json DEFAULT NULL,
  `webhook_data` json DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `charge_id` (`charge_id`),
  KEY `order_id` (`order_id`),
  KEY `status_created_at` (`status`,`created_at`)
);
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the Repository**
   ```bash
   git fork https://github.com/thusithawijethunga/botble-genie-payment.git
   ```

2. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Changes**
   - Follow PSR-12 coding standards
   - Add unit tests for new features
   - Update documentation

4. **Submit Pull Request**
   - Ensure all tests pass
   - Provide clear description of changes
   - Reference any related issues

### Development Setup

```bash
# Clone repository
git clone https://github.com/thusithawijethunga/botble-genie-payment.git
cd genie-payment

# Install dependencies
composer install

# Set up testing environment
cp .env.example .env.testing
php artisan key:generate --env=testing

# Run tests
php artisan test
```

## 📝 Changelog

### Version 1.0.0 (2024-01-01)
- ✨ Initial release
- ✨ Complete payment gateway integration
- ✨ Webhook support
- ✨ Transaction management
- ✨ Admin dashboard integration
- ✨ Multi-currency support
- ✨ Comprehensive logging
- ✨ Sandbox environment support

### Planned Features
- 🔄 Automated refund processing
- 📊 Advanced analytics dashboard
- 🌐 Multi-language support
- 📱 Mobile app integration
- 💼 Merchant dashboard enhancements

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 Genie Business Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

## 🆘 Support

### Technical Support
- **Email**: [contact@byheart.lk](mailto:contact@byheart.lk)
- **Phone**: +94 722 983 616
- **Hours**: Monday - Friday, 9:00 AM - 6:00 PM (GMT+5:30)

### General Inquiries
- **Email**: [Geniemerchantsupport@dialog.lk](mailto:Geniemerchantsupport@dialog.lk)
- **Phone**: 076 076 0760

### Documentation & Resources
- **API Documentation**: [https://geniebusiness.stoplight.io/](https://geniebusiness.stoplight.io/)
- **Merchant Dashboard**: [https://dashboard.geniebiz.lk](https://dashboard.geniebiz.lk)
- **Botble CMS**: [https://botble.com](https://botble.com)

### Community
- **Issues**: [GitHub Issues](https://github.com/thusithawijethunga/botble-genie-payment/issues)
- **Discussions**: [GitHub Discussions](https://github.com/thusithawijethunga/botble-genie-payment/discussions)
- **Discord**: [Botble Community](https://discord.gg/botble)

## 🏢 About

**Genie Business** is a comprehensive payment solution by Dialog Axiata PLC, Sri Lanka's premier connectivity provider. This plugin enables seamless integration between Botble CMS and Genie Business payment infrastructure.

**Botble CMS** is a modern, fast, and flexible content management system built on Laravel framework, perfect for creating professional websites and applications.

---

<div align="center">

**Made with ❤️ for the Sri Lankan e-commerce community**

[🌐 Website](https://geniebiz.lk) • [📧 Email](mailto:genie.integration@dialog.lk) • [📱 Download App](https://geniebiz.lk/download)

</div>