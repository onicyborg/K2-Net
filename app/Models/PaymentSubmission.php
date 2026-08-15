<?php

namespace App\Models;

use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentSubmission extends Model
{
    use HasUuidV7;

    protected $table = 'payment_submissions';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'status',
        'bank',
        'account_number',
        'account_name',
        'transfer_amount',
        'transfer_from',
        'transfer_date',
        'payment_proof_id',
        'rejection_reason',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'transfer_amount' => 'integer',
            'transfer_date'   => 'date',
            'submitted_at'    => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'payment_submission_invoices')
            ->withPivot('invoice_id');
    }

    public function paymentProof(): BelongsTo
    {
        return $this->belongsTo(PaymentProof::class, 'payment_proof_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'menunggu_verifikasi';
    }

    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi' => '<span class="badge badge-warning">Menunggu Verifikasi</span>',
            'disetujui'          => '<span class="badge badge-success">Disetujui</span>',
            'ditolak'            => '<span class="badge badge-danger">Ditolak</span>',
            default              => '<span class="badge badge-secondary">' . e($this->status) . '</span>',
        };
    }

    public function formattedAmount(): string
    {
        return 'Rp' . number_format($this->transfer_amount, 0, ',', '.');
    }

    public function billingPeriods(): string
    {
        $periods = $this->invoices()
            ->orderBy('billing_period')
            ->pluck('billing_period')
            ->map(fn ($p) => \Carbon\Carbon::parse($p)->format('M Y'))
            ->toArray();

        if (count($periods) <= 3) {
            return implode(', ', $periods);
        }

        return $periods[0] . ' – ' . end($periods) . ' (' . count($periods) . ' bulan)';
    }
}
