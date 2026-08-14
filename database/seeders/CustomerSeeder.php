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
                'email' => 'budi.santoso@gmail.com',
                'whatsapp_number' => '081234567890',
                'whatsapp_number_full' => '6281234567890',
                'address' => 'Jl. Melati No. 12, RT 003/RW 005, Kelurahan Sukamaju, Kecamatan Cilandak, Jakarta Selatan 12430',
                'package_name' => 'Paket Standard',
                'status' => CustomerStatus::AKTIF->value,
                'notes' => 'Pelanggan tetap sejak Januari 2024. Tidak pernah terlambat bayar.',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@yahoo.co.id',
                'whatsapp_number' => '087654321098',
                'whatsapp_number_full' => '6287654321098',
                'address' => 'Perum Griya Asri Blok B-15, RT 001/RW 008, Kelurahan Margahayu, Kecamatan Bebesan, Bandung 40111',
                'package_name' => 'Paket Premium',
                'status' => CustomerStatus::AKTIF->value,
                'notes' => 'Milik usaha home business. Sering upload video, butuh bandwidth stabil.',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@outlook.com',
                'whatsapp_number' => '082211334455',
                'whatsapp_number_full' => '6282211334455',
                'address' => 'Jl. Diponegoro No. 88, RT 005/RW 002, Kelurahan Kalibaru, Kecamatan Cilacap Tengah, Cilacap 53212',
                'package_name' => 'Paket Basic',
                'status' => CustomerStatus::ISOLIR->value,
                'notes' => 'Tagihan Mei & Juni 2026 belum dibayar. Akan dinormalkan setelah pelunasan.',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'whatsapp_number' => '085612345678',
                'whatsapp_number_full' => '6285612345678',
                'address' => 'Perumahan Green Hills Cluster Palm No. 7, RT 004/RW 003, Kelurahan Jeruk, Kecamatan Jakarta Barat, Jakarta Barat 11610',
                'package_name' => 'Paket Business',
                'status' => CustomerStatus::AKTIF->value,
                'notes' => 'Pemilik kantor desain grafis. Paket bisnis untuk 10 komputer.',
            ],
            [
                'name' => 'Rizky Ramadhan',
                'email' => 'rizky.ramadhan@gmail.com',
                'whatsapp_number' => '081987654321',
                'whatsapp_number_full' => '6281987654321',
                'address' => 'Jl. Keadilan No. 45, RT 002/RW 007, Kelurahan Kebon Jeruk, Kecamatan Kebon Jeruk, Jakarta Barat 11530',
                'package_name' => 'Paket Standard',
                'status' => CustomerStatus::NONAKTIF->value,
                'notes' => 'Sudah tidak aktif sejak Juli 2026. Permintaan pelanggan.',
            ],
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
