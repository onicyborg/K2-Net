<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $packages = Package::all()->keyBy('name');
        $adminUser = User::first();

        if ($packages->isEmpty()) {
            $this->command->warn('PackageSeeder harus dijalankan dulu.');
            return;
        }

        $customers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'blackboy.ziee@gmail.com',
                'whatsapp_number' => '081234567890',
                'whatsapp_number_full' => '6281234567890',
                'address' => 'Jl. Melati No. 12, RT 003/RW 005, Kelurahan Sukamaju, Kecamatan Cilandak, Jakarta Selatan 12430',
                'package_name' => 'Paket Standard',
                'status' => CustomerStatus::AKTIF->value,
                'notes' => 'Pelanggan tetap sejak Januari 2024. Tidak pernah terlambat bayar.',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'geatsmark009@gmail.com',
                'whatsapp_number' => '087654321098',
                'whatsapp_number_full' => '6287654321098',
                'address' => 'Perum Griya Asri Blok B-15, RT 001/RW 008, Kelurahan Margahayu, Kecamatan Bebesan, Bandung 40111',
                'package_name' => 'Paket Premium',
                'status' => CustomerStatus::AKTIF->value,
                'notes' => 'Milik usaha home business. Sering upload video, butuh bandwidth stabil.',
            ],
            [
                'name' => 'Akhmad Fauzi',
                'email' => 'akhmadfauzy40@gmail.com',
                'whatsapp_number' => '082211334455',
                'whatsapp_number_full' => '6282211334455',
                'address' => 'Jl. Diponegoro No. 88, RT 005/RW 002, Kelurahan Kalibaru, Kecamatan Cilacap Tengah, Cilacap 53212',
                'package_name' => 'Paket Basic',
                'status' => CustomerStatus::AKTIF->value,
                'notes' => 'Tagihan Mei & Juni 2026 belum dibayar. Akan dinormalkan setelah pelunasan.',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'cendanak2net@gmail.com',
                'whatsapp_number' => '085612345678',
                'whatsapp_number_full' => '6285612345678',
                'address' => 'Perumahan Green Hills Cluster Palm No. 7, RT 004/RW 003, Kelurahan Jeruk, Kecamatan Jakarta Barat, Jakarta Barat 11610',
                'package_name' => 'Paket Business',
                'status' => CustomerStatus::AKTIF->value,
                'notes' => 'Pemilik kantor desain grafis. Paket bisnis untuk 10 komputer.',
            ]
        ];

        foreach ($customers as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'pelanggan',
            ]);

            Customer::create([
                'user_id' => $user->id,
                'code' => 'CUST-' . strtoupper(substr(md5($data['email']), 0, 6)),
                'name' => $data['name'],
                'email' => $data['email'],
                'whatsapp_number' => $data['whatsapp_number'],
                'whatsapp_number_full' => $data['whatsapp_number_full'],
                'address' => $data['address'],
                'package_id' => $packages[$data['package_name']]->id,
                'status' => $data['status'],
                'notes' => $data['notes'],
                'portal_code' => Str::random(16),
            ]);
        }
    }
}
