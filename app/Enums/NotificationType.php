<?php

namespace App\Enums;

enum NotificationType: string
{
    case REMINDER_H3 = 'reminder_h3';
    case REMINDER_H0 = 'reminder_h0';
    case REMINDER_H3_LATE = 'reminder_h3_late';
    case CONFIRMATION = 'confirmation';
    case REJECTION = 'rejection';

    public function label(): string
    {
        return match ($this) {
            self::REMINDER_H3 => 'Pengingat H-3',
            self::REMINDER_H0 => 'Pengingat Jatuh Tempo',
            self::REMINDER_H3_LATE => 'Pengingat Terlambat',
            self::CONFIRMATION => 'Konfirmasi Pembayaran',
            self::REJECTION => 'Penolakan Pembayaran',
        };
    }
}
