<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PELANGGAN = 'pelanggan';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::PELANGGAN => 'Pelanggan',
        };
    }
}
