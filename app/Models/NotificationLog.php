<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory, HasUuidV7;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'invoice_id',
        'customer_id',
        'notification_type',
        'channel',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'pending' => ['class' => 'info', 'label' => 'Pending'],
            'sent' => ['class' => 'success', 'label' => 'Terkirim'],
            'failed' => ['class' => 'danger', 'label' => 'Gagal'],
            default => ['class' => 'secondary', 'label' => $this->status],
        };
    }

    public function typeLabel(): string
    {
        return match ($this->notification_type) {
            'reminder_h3'      => 'Invoice Baru',
            'reminder_before'  => 'Pengingat Sebelum Jatuh Tempo',
            'reminder_overdue' => 'Pengingat Lewat Jatuh Tempo',
            'confirmation'     => 'Konfirmasi Pembayaran',
            'rejection'        => 'Penolakan Pembayaran',
            default            => $this->notification_type,
        };
    }
}
