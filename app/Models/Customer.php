<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'whatsapp_number',
        'whatsapp_number_full',
        'email',
        'address',
        'package_id',
        'status',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', CustomerStatus::AKTIF->value);
    }

    public function isActive(): bool
    {
        return $this->status === CustomerStatus::AKTIF->value;
    }

    public function isIsolir(): bool
    {
        return $this->status === CustomerStatus::ISOLIR->value;
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'aktif' => ['class' => 'success', 'label' => 'Aktif'],
            'isolir' => ['class' => 'warning', 'label' => 'Isolir'],
            'nonaktif' => ['class' => 'dark', 'label' => 'Nonaktif'],
            default => ['class' => 'secondary', 'label' => $this->status],
        };
    }
}
