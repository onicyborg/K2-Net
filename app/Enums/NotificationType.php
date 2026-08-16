<?php

namespace App\Enums;

enum NotificationType: string
{
    case REMINDER_BEFORE = 'reminder_before';
    case REMINDER_OVERDUE = 'reminder_overdue';
    case CONFIRMATION = 'confirmation';
    case REJECTION = 'rejection';

    /**
     * Notifikasi tagihan aktif di awal bulan (tanggal 1).
     * Dipakai oleh command billing:send-reminders.
     */
    case BILLING_REMINDER_ACTIVE = 'billing_reminder_active';

    /**
     * Notifikasi peringatan jatuh tempo tanggal 15.
     * Dipakai oleh command billing:send-reminders.
     */
    case BILLING_REMINDER_DUE = 'billing_reminder_due';

    public function label(): string
    {
        return match ($this) {
            self::REMINDER_BEFORE         => 'Pengingat Sebelum Jatuh Tempo',
            self::REMINDER_OVERDUE        => 'Pengingat Lewat Jatuh Tempo',
            self::CONFIRMATION            => 'Konfirmasi Pembayaran',
            self::REJECTION               => 'Penolakan Pembayaran',
            self::BILLING_REMINDER_ACTIVE => 'Pengingat Tagihan Aktif (Tgl 1)',
            self::BILLING_REMINDER_DUE    => 'Pengingat Jatuh Tempo (Tgl 15)',
        };
    }
}
