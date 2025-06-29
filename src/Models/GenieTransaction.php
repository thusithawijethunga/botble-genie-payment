<?php

namespace Botble\GeniePayment\Models;

use Botble\Base\Models\BaseModel;
use Carbon\Carbon;

class GenieTransaction extends BaseModel
{
    protected $table = 'genie_payment_transactions';

    protected $fillable = [
        'transaction_id',
        'charge_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'payment_url',
        'short_url',
        'customer_id',
        'customer_type',
        'expires_at',
        'verified_at',
        'webhook_received_at',
        'payment_data',
        'api_response',
        'webhook_data',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'webhook_received_at' => 'datetime',
        'payment_data' => 'array',
        'api_response' => 'array',
        'webhook_data' => 'array',
    ];

    // Status constants
    const STATUS_INITIATED = 'initiated';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REFUNDED = 'refunded';

    // Relationships
    public function customer()
    {
        if ($this->customer_type && $this->customer_id) {
            return $this->morphTo('customer', 'customer_type', 'customer_id');
        }
        return null;
    }

    // Status check methods
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CONFIRMED, self::STATUS_AUTHORIZED]);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED]);
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_INITIATED, self::STATUS_PENDING]);
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_INITIATED => 'Initiated',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_AUTHORIZED => 'Authorized', 
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_REFUNDED => 'Refunded',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED, self::STATUS_CONFIRMED, self::STATUS_AUTHORIZED => 'success',
            self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED => 'danger',
            self::STATUS_PENDING, self::STATUS_INITIATED => 'warning',
            self::STATUS_REFUNDED => 'info',
            default => 'secondary',
        };
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, $customerId, string $customerType = null)
    {
        $query->where('customer_id', $customerId);
        
        if ($customerType) {
            $query->where('customer_type', $customerType);
        }
        
        return $query;
    }

    public function scopeByOrderId($query, string $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByTransactionId($query, string $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_CONFIRMED, self::STATUS_AUTHORIZED]);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED]);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_INITIATED, self::STATUS_PENDING]);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CONFIRMED, self::STATUS_AUTHORIZED]);
    }

    // Mutators
    public function setPaymentDataAttribute($value)
    {
        $this->attributes['payment_data'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setApiResponseAttribute($value)
    {
        $this->attributes['api_response'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setWebhookDataAttribute($value)
    {
        $this->attributes['webhook_data'] = is_array($value) ? json_encode($value) : $value;
    }

    // Helper methods
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'verified_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason = null): bool
    {
        $updateData = ['status' => self::STATUS_FAILED];
        
        if ($reason) {
            $updateData['notes'] = $reason;
        }
        
        return $this->update($updateData);
    }

    public function markAsExpired(): bool
    {
        return $this->update(['status' => self::STATUS_EXPIRED]);
    }

    public function addNote(string $note): bool
    {
        $existingNotes = $this->notes ? $this->notes . "\n" : '';
        return $this->update(['notes' => $existingNotes . Carbon::now()->format('Y-m-d H:i:s') . ': ' . $note]);
    }

    // Static methods
    public static function findByTransactionId(string $transactionId): ?self
    {
        return static::where('transaction_id', $transactionId)->first();
    }

    public static function findByOrderId(string $orderId): ?self
    {
        return static::where('order_id', $orderId)->first();
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_INITIATED => 'Initiated',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_AUTHORIZED => 'Authorized',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    public static function getSuccessStatuses(): array
    {
        return [self::STATUS_COMPLETED, self::STATUS_CONFIRMED, self::STATUS_AUTHORIZED];
    }

    public static function getFailureStatuses(): array
    {
        return [self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED];
    }

    public static function getPendingStatuses(): array
    {
        return [self::STATUS_INITIATED, self::STATUS_PENDING];
    }
}