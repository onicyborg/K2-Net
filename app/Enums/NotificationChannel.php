<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';

    public function label(): string
    {
        return match ($this) {
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'Email',
        };
    }
}
