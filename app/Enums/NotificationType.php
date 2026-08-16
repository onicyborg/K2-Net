<?php

namespace App\Enums;

enum NotificationType: string
{
    case REMINDER_BEFORE = 'reminder_before';
    case REMINDER_OVERDUE = 'reminder_overdue';
    case CONFIRMATION = 'confirmation';
    case REJECTION = 'rejection';

    public function label(): string
    {
        return match ($this) {
            self::REMINDER_BEFORE  => 'Pengingat Sebelum Jatuh Tempo',
            self::REMINDER_OVERDUE => 'Pengingat Lewat Jatuh Tempo',
            self::CONFIRMATION     => 'Konfirmasi Pembayaran',
            self::REJECTION        => 'Penolakan Pembayaran',
        };
    }
}
