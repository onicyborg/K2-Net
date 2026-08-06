<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case AKTIF = 'aktif';
    case ISOLIR = 'isolir';
    case NONAKTIF = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::ISOLIR => 'Isolir',
            self::NONAKTIF => 'Nonaktif',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AKTIF => 'success',
            self::ISOLIR => 'warning',
            self::NONAKTIF => 'dark',
        };
    }
}
