<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case BELUM_BAYAR = 'belum_bayar';
    case MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    case LUNAS = 'lunas';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::BELUM_BAYAR => 'Belum Bayar',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::LUNAS => 'Lunas',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BELUM_BAYAR => 'danger',
            self::MENUNGGU_VERIFIKASI => 'warning',
            self::LUNAS => 'success',
            self::DITOLAK => 'dark',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::BELUM_BAYAR => $newStatus === self::MENUNGGU_VERIFIKASI,
            self::MENUNGGU_VERIFIKASI => in_array($newStatus, [self::LUNAS, self::BELUM_BAYAR]),
            self::LUNAS, self::DITOLAK => false,
        };
    }
}
