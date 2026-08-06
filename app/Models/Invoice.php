<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'billing_period',
        'amount',
        'due_date',
        'status',
        'rejection_reason',
        'issued_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'billing_period' => 'date',
            'due_date' => 'date',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'amount' => 'decimal:0',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentProof(): HasOne
    {
        return $this->hasOne(PaymentProof::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function scopeBelumBayar($query)
    {
        return $query->where('status', 'belum_bayar');
    }

    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }

    public function scopeMenungguVerifikasi($query)
    {
        return $query->where('status', 'menunggu_verifikasi');
    }

    public function formattedAmount(): string
    {
        return 'Rp' . number_format($this->amount, 0, ',', '.');
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'belum_bayar' => ['class' => 'danger', 'label' => 'Belum Bayar'],
            'menunggu_verifikasi' => ['class' => 'warning', 'label' => 'Menunggu Verifikasi'],
            'lunas' => ['class' => 'success', 'label' => 'Lunas'],
            'ditolak' => ['class' => 'dark', 'label' => 'Ditolak'],
            default => ['class' => 'secondary', 'label' => $this->status],
        };
    }

    public function isOverdue(): bool
    {
        return $this->status === 'belum_bayar' && $this->due_date->isPast();
    }

    public function daysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }
}
