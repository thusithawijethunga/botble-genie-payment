<?php
// platform\plugins\genie-payment\database\migrations\2024_01_01_000000_create_genie_payment_transactions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('genie_payment_transactions')) {
            Schema::create('genie_payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id')->nullable()->index()->comment('Genie Business transaction ID');
                $table->string('charge_id')->nullable()->index()->comment('Botble payment charge ID');
                $table->string('order_id')->index()->comment('Local order/reference ID');
                $table->decimal('amount', 15, 2)->comment('Transaction amount');
                $table->string('currency', 3)->default('LKR')->comment('Transaction currency');
                $table->string('status')->default('initiated')->index()->comment('Transaction status');
                $table->text('payment_url')->nullable()->comment('Payment page URL');
                $table->text('short_url')->nullable()->comment('Short payment URL');
                $table->string('customer_id')->nullable()->index()->comment('Customer ID');
                $table->string('customer_type')->nullable()->comment('Customer model type');
                $table->timestamp('expires_at')->nullable()->comment('Payment link expiry time');
                $table->timestamp('verified_at')->nullable()->comment('When transaction was verified');
                $table->timestamp('webhook_received_at')->nullable()->comment('When webhook was received');
                $table->json('payment_data')->nullable()->comment('Original payment request data');
                $table->json('api_response')->nullable()->comment('API response data');
                $table->json('webhook_data')->nullable()->comment('Webhook payload data');
                $table->text('notes')->nullable()->comment('Additional notes');
                $table->timestamps();

                // Indexes for better performance
                $table->index(['status', 'created_at']);
                $table->index(['currency', 'created_at']);
                $table->index(['customer_id', 'customer_type']);
                $table->index(['expires_at']);
                $table->index(['verified_at']);
                $table->index(['webhook_received_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genie_payment_transactions');
    }
};