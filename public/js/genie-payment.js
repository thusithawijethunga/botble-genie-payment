/**
 * Genie Business Payment Gateway - Frontend JavaScript
 * Handles payment processing, status checking, and user interactions
 */

class GeniePaymentHandler {
    constructor(options = {}) {
        this.options = {
            statusCheckInterval: 5000, // 5 seconds
            maxStatusChecks: 60, // 5 minutes total
            apiBaseUrl: '/payment/genie',
            ...options
        };
        
        this.statusCheckCount = 0;
        this.statusCheckTimer = null;
        this.currentTransactionId = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupCSRFToken();
    }

    setupCSRFToken() {
        // Setup CSRF token for AJAX requests
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token.getAttribute('content')
                }
            });
        }
    }

    bindEvents() {
        // Payment method selection
        $(document).on('change', 'input[name="payment_method"]', (e) => {
            if (e.target.value === 'genie_payment') {
                this.showGeniePaymentInfo();
            }
        });

        // Payment form submission
        $(document).on('submit', '.payment-checkout-form', (e) => {
            const selectedMethod = $('input[name="payment_method"]:checked').val();
            if (selectedMethod === 'genie_payment') {
                this.handlePaymentSubmission(e);
            }
        });

        // Transaction status check buttons
        $(document).on('click', '.check-payment-status', (e) => {
            e.preventDefault();
            const transactionId = $(e.target).data('transaction-id');
            this.checkTransactionStatus(transactionId);
        });

        // Cancel payment buttons
        $(document).on('click', '.cancel-payment', (e) => {
            e.preventDefault();
            const transactionId = $(e.target).data('transaction-id');
            this.cancelPayment(transactionId);
        });

        // Retry payment buttons
        $(document).on('click', '.retry-payment', (e) => {
            e.preventDefault();
            window.location.reload();
        });
    }

    showGeniePaymentInfo() {
        // Show additional information when Genie payment is selected
        const genieInfo = $('.payment_genie_payment_wrap');
        if (genieInfo.length) {
            genieInfo.slideDown();
            this.displayAcceptedCards();
        }
    }

    displayAcceptedCards() {
        // Display accepted card types and security information
        const cardInfo = `
            <div class="genie-payment-info mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Accepted Cards</h6>
                        <div class="card-types">
                            <i class="fab fa-cc-visa text-primary" style="font-size: 2rem;"></i>
                            <i class="fab fa-cc-mastercard text-warning ms-2" style="font-size: 2rem;"></i>
                            <i class="fab fa-cc-amex text-info ms-2" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Security Features</h6>
                        <div class="security-features">
                            <small class="text-muted d-block">
                                <i class="fa fa-shield-alt text-success"></i> SSL Encrypted
                            </small>
                            <small class="text-muted d-block">
                                <i class="fa fa-lock text-success"></i> Secure Processing
                            </small>
                            <small class="text-muted d-block">
                                <i class="fa fa-check text-success"></i> PCI Compliant
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        if (!$('.genie-payment-info').length) {
            $('.payment_genie_payment_wrap').append(cardInfo);
        }
    }

    handlePaymentSubmission(e) {
        const form = $(e.target);
        const submitButton = form.find('button[type="submit"]');
        
        // Show loading state
        this.showLoadingState(submitButton);
        
        // Store form reference for potential retry
        this.currentForm = form;
        
        // Let the form submit normally - Botble will handle the redirect to Genie
        return true;
    }

    showLoadingState(button) {
        const originalText = button.text();
        button.data('original-text', originalText);
        button.html('<i class="fa fa-spinner fa-spin"></i> Processing Payment...');
        button.prop('disabled', true);
    }

    hideLoadingState(button) {
        const originalText = button.data('original-text') || 'Pay Now';
        button.html(originalText);
        button.prop('disabled', false);
    }

    startTransactionStatusCheck(transactionId) {
        this.currentTransactionId = transactionId;
        this.statusCheckCount = 0;
        
        // Clear any existing timer
        if (this.statusCheckTimer) {
            clearInterval(this.statusCheckTimer);
        }
        
        // Start checking status
        this.statusCheckTimer = setInterval(() => {
            this.checkTransactionStatus(transactionId);
        }, this.options.statusCheckInterval);
        
        // Also check immediately
        this.checkTransactionStatus(transactionId);
    }

    async checkTransactionStatus(transactionId) {
        try {
            const response = await $.ajax({
                url: `${this.options.apiBaseUrl}/transaction-status`,
                method: 'POST',
                data: { transaction_id: transactionId },
                dataType: 'json'
            });

            if (response.success) {
                this.updateStatusDisplay(response.data);
                
                // Stop checking if transaction is completed or failed
                if (response.data.is_completed || response.data.is_failed) {
                    this.stopStatusCheck();
                    this.handleFinalStatus(response.data);
                }
            } else {
                console.error('Status check failed:', response.message);
            }
        } catch (error) {
            console.error('Error checking transaction status:', error);
        }
        
        // Stop checking after max attempts
        this.statusCheckCount++;
        if (this.statusCheckCount >= this.options.maxStatusChecks) {
            this.stopStatusCheck();
            this.showTimeoutMessage();
        }
    }

    stopStatusCheck() {
        if (this.statusCheckTimer) {
            clearInterval(this.statusCheckTimer);
            this.statusCheckTimer = null;
        }
    }

    updateStatusDisplay(data) {
        const statusElement = $(`.transaction-status[data-transaction-id="${data.transaction_id}"]`);
        
        if (statusElement.length) {
            // Update status badge
            const statusBadge = statusElement.find('.status-badge');
            statusBadge.removeClass('badge-warning badge-success badge-danger')
                     .addClass(this.getStatusBadgeClass(data.status))
                     .text(data.status_label);
            
            // Update timestamp
            statusElement.find('.last-updated').text(`Last updated: ${new Date().toLocaleTimeString()}`);
        }
        
        // Update page title with status
        if (data.is_completed) {
            document.title = 'Payment Successful - ' + document.title;
        } else if (data.is_failed) {
            document.title = 'Payment Failed - ' + document.title;
        }
    }

    getStatusBadgeClass(status) {
        switch (status.toLowerCase()) {
            case 'completed':
            case 'confirmed':
            case 'authorized':
                return 'badge-success';
            case 'failed':
            case 'cancelled':
            case 'expired':
                return 'badge-danger';
            default:
                return 'badge-warning';
        }
    }

    handleFinalStatus(data) {
        if (data.is_completed) {
            this.showSuccessMessage(data);
            // Redirect to success page after a delay
            setTimeout(() => {
                window.location.href = '/account/packages';
            }, 3000);
        } else if (data.is_failed) {
            this.showFailureMessage(data);
        }
    }

    showSuccessMessage(data) {
        const message = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5><i class="fa fa-check-circle"></i> Payment Successful!</h5>
                <p>Your payment of ${data.amount} has been processed successfully.</p>
                <p><small>Transaction ID: ${data.transaction_id}</small></p>
                <p><small>You will be redirected to your dashboard shortly...</small></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        this.showMessage(message);
    }

    showFailureMessage(data) {
        const message = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5><i class="fa fa-times-circle"></i> Payment Failed</h5>
                <p>Unfortunately, your payment could not be processed.</p>
                <p><small>Status: ${data.status_label}</small></p>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary retry-payment">Try Again</button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Go Back</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        this.showMessage(message);
    }

    showTimeoutMessage() {
        const message = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5><i class="fa fa-clock"></i> Status Check Timeout</h5>
                <p>We're still processing your payment. Please check back in a few minutes or contact support.</p>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary check-payment-status" data-transaction-id="${this.currentTransactionId}">Check Status Again</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        this.showMessage(message);
    }

    showMessage(messageHtml) {
        // Remove existing messages
        $('.payment-message-container').remove();
        
        // Add message container
        const container = `<div class="payment-message-container">${messageHtml}</div>`;
        $('body').prepend(container);
        
        // Auto-hide success messages after 10 seconds
        setTimeout(() => {
            $('.alert-success').fadeOut();
        }, 10000);
    }

    async cancelPayment(transactionId) {
        if (!confirm('Are you sure you want to cancel this payment?')) {
            return;
        }
        
        try {
            const response = await $.ajax({
                url: `${this.options.apiBaseUrl}/cancel`,
                method: 'POST',
                data: { transaction_id: transactionId },
                dataType: 'json'
            });

            if (response.success) {
                this.showMessage(`
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h5><i class="fa fa-info-circle"></i> Payment Cancelled</h5>
                        <p>${response.message}</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                
                // Refresh status
                this.checkTransactionStatus(transactionId);
            } else {
                alert('Error: ' + response.message);
            }
        } catch (error) {
            console.error('Error cancelling payment:', error);
            alert('An error occurred while cancelling the payment. Please try again.');
        }
    }

    // Utility method to format currency
    formatCurrency(amount, currency = 'LKR') {
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2
        });
        
        return formatter.format(amount);
    }

    // Method to validate form before submission
    validatePaymentForm(form) {
        const requiredFields = form.find('[required]');
        let isValid = true;
        
        requiredFields.each(function() {
            const field = $(this);
            if (!field.val().trim()) {
                field.addClass('is-invalid');
                isValid = false;
            } else {
                field.removeClass('is-invalid');
            }
        });
        
        return isValid;
    }

    // Method to show validation errors
    showValidationErrors(errors) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5><i class="fa fa-exclamation-triangle"></i> Validation Errors</h5>
                <ul class="mb-0">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        this.showMessage(errorHtml);
    }
}

// Initialize when document is ready
$(document).ready(function() {
    // Initialize Genie Payment Handler
    window.geniePaymentHandler = new GeniePaymentHandler();
    
    // Auto-start status checking if transaction ID is present in URL
    const urlParams = new URLSearchParams(window.location.search);
    const transactionId = urlParams.get('transactionId') || urlParams.get('transaction_id');
    
    if (transactionId) {
        window.geniePaymentHandler.startTransactionStatusCheck(transactionId);
    }
    
    // Handle page visibility change to pause/resume status checking
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, stop status checking to save resources
            window.geniePaymentHandler.stopStatusCheck();
        } else if (window.geniePaymentHandler.currentTransactionId) {
            // Page is visible again, resume status checking
            window.geniePaymentHandler.startTransactionStatusCheck(
                window.geniePaymentHandler.currentTransactionId
            );
        }
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GeniePaymentHandler;
}