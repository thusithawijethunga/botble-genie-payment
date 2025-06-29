<?php

use Botble\GeniePayment\Http\Controllers\GeniePaymentController;
use Illuminate\Support\Facades\Route;

Route::group(['controller' => GeniePaymentController::class, 'middleware' => ['web', 'core']], function () {
    // Public routes
    Route::get('payment/genie/status', 'getCallback')->name('payments.genie.status');
    Route::post('payment/genie/webhook', 'webhook')->name('payments.genie.webhook');
    
    // AJAX routes
    Route::post('payment/genie/transaction-status', 'getTransactionStatus')->name('payments.genie.transaction-status');
    Route::post('payment/genie/cancel', 'cancelPayment')->name('payments.genie.cancel');
    
    // Admin routes
    Route::middleware(['auth', 'permission:genie-payment.index'])->group(function () {
        Route::get('admin/genie-payment/transaction/{id}', 'getTransactionDetails')
            ->name('payments.genie.admin.transaction-details');
    });
});