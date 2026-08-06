<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Terkirim',
            self::FAILED => 'Gagal',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'info',
            self::SENT => 'success',
            self::FAILED => 'danger',
        };
    }
}
