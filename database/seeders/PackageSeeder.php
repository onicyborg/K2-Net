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
                'name' => 'Paket Basic',
                'speed' => '5 Mbps',
                'price' => 100000,
                'description' => 'Paket internet basic untuk rumah tangga',
                'is_active' => true,
            ],
            [
                'name' => 'Paket Standard',
                'speed' => '10 Mbps',
                'price' => 150000,
                'description' => 'Paket internet standard untuk rumah tangga',
                'is_active' => true,
            ],
            [
                'name' => 'Paket Premium',
                'speed' => '20 Mbps',
                'price' => 250000,
                'description' => 'Paket internet premium untuk rumah tangga',
                'is_active' => true,
            ],
            [
                'name' => 'Paket Business',
                'speed' => '50 Mbps',
                'price' => 500000,
                'description' => 'Paket internet untuk usaha kecil-menengah',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
