# Project Structure

This file maintains an up-to-date list of project files and structure.

## File Structure

- 📁 `.vscode/`
- 📁 `config/`
  - 📄 `config\permissions.php` (Unknown)
- 📁 `database/`
  - 📁 `database\migrations/`
    - 📄 `database\migrations\2024_01_01_000000_create_genie_payment_transactions_table.php` (Unknown)
    - 📄 `database\migrations\2024_01_01_000001_create_genie_payment_logs_table.php` (Unknown)
- 📁 `helpers/`
  - 📄 `helpers\constants.php` (Unknown)
- 📁 `public/`
  - 📁 `public\css/`
    - 📄 `public\css\genie-payment.css` (CSS)
  - 📁 `public\images/`
    - 📄 `public\images\genie-logo.png` (Unknown)
  - 📁 `public\js/`
    - 📄 `public\js\genie-payment.js` (JavaScript)
      - *Exports:* `module.exports`
- 📁 `resources/`
  - 📁 `resources\lang/`
    - 📁 `resources\lang\en/`
      - 📄 `resources\lang\en\genie-payment.php` (Unknown)
  - 📁 `resources\views/`
    - 📄 `resources\views\detail.blade.php` (Unknown)
    - 📄 `resources\views\email.blade.php` (Unknown)
    - 📄 `resources\views\methods.blade.php` (Unknown)
    - 📄 `resources\views\settings.blade.php` (Unknown)
- 📁 `routes/`
  - 📄 `routes\web.php` (Unknown)
- 📁 `src/`
  - 📁 `src\Client/`
    - 📁 `src\Client\Crypt/`
      - 📄 `src\Client\Crypt\Signature.php` (Unknown)
      - 📄 `src\Client\Crypt\Verify.php` (Unknown)
    - 📁 `src\Client\Model/`
      - 📄 `src\Client\Model\Transaction.php` (Unknown)
    - 📁 `src\Client\Options/`
      - 📄 `src\Client\Options\Expires.php` (Unknown)
    - 📄 `src\Client\Client.php` (Unknown)
    - 📄 `src\Client\Transactions.php` (Unknown)
  - 📁 `src\Http/`
    - 📁 `src\Http\Controllers/`
      - 📄 `src\Http\Controllers\GeniePaymentController.php` (Unknown)
    - 📁 `src\Http\Requests/`
      - 📄 `src\Http\Requests\GeniePaymentCallbackRequest.php` (Unknown)
  - 📁 `src\Models/`
    - 📄 `src\Models\GenieTransaction.php` (Unknown)
  - 📁 `src\Providers/`
    - 📄 `src\Providers\GeniePaymentServiceProvider.php` (Unknown)
    - 📄 `src\Providers\HookServiceProvider.php` (Unknown)
  - 📁 `src\Services/`
    - 📁 `src\Services\Abstracts/`
      - 📄 `src\Services\Abstracts\GeniePaymentAbstract.php` (Unknown)
    - 📁 `src\Services\Gateways/`
      - 📄 `src\Services\Gateways\GeniePaymentService.php` (Unknown)
  - 📄 `src\Plugin.php` (Unknown)
- 📄 `composer.json` (JSON)
- 📄 `plugin.json` (JSON)
- 📄 `README.md` (Markdown)
- 📄 `screenshot.png` (Unknown)

---
Last updated: 2025-06-29T09:27:07.735Z
