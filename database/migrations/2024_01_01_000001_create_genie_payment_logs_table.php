<?php

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

        if (!Schema::hasTable('genie_payment_logs')) {
            Schema::create('genie_payment_logs', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id')->nullable()->index();
                $table->string('type')->index()->comment('Log type: request, response, webhook, error');
                $table->string('event')->comment('Event name');
                $table->text('message')->nullable()->comment('Log message');
                $table->json('data')->nullable()->comment('Log data');
                $table->string('ip_address')->nullable()->comment('Client IP address');
                $table->string('user_agent')->nullable()->comment('Client user agent');
                $table->timestamps();

                $table->index(['type', 'created_at']);
                $table->index(['event', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genie_payment_logs');
    }
};