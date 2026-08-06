<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'id' => 'K2-PKG-01HQ000000000000001',
                'name' => 'Paket Basic',
                'speed' => '5 Mbps',
                'price' => 100000,
                'description' => 'Paket internet basic untuk rumah tangga',
                'is_active' => true,
            ],
            [
                'id' => 'K2-PKG-01HQ000000000000002',
                'name' => 'Paket Standard',
                'speed' => '10 Mbps',
                'price' => 150000,
                'description' => 'Paket internet standard untuk rumah tangga',
                'is_active' => true,
            ],
            [
                'id' => 'K2-PKG-01HQ000000000000003',
                'name' => 'Paket Premium',
                'speed' => '20 Mbps',
                'price' => 250000,
                'description' => 'Paket internet premium untuk rumah tangga',
                'is_active' => true,
            ],
            [
                'id' => 'K2-PKG-01HQ000000000000004',
                'name' => 'Paket Business',
                'speed' => '50 Mbps',
                'price' => 500000,
                'description' => 'Paket internet untuk usaha kecil-menengah',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['id' => $package['id']],
                $package
            );
        }
    }
}
