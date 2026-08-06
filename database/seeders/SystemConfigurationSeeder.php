<?php

namespace Database\Seeders;

use App\Models\SystemConfiguration;
use Illuminate\Database\Seeder;

class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // Billing
            [
                'id' => 'K2-CFG-01HQ000000000000001',
                'key' => 'invoice_generate_day',
                'value' => '25',
                'type' => 'number',
                'description' => 'Tanggal generate invoice bulanan',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000002',
                'key' => 'invoice_due_day',
                'value' => '5',
                'type' => 'number',
                'description' => 'Tanggal jatuh tempo (bulan berikutnya)',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000003',
                'key' => 'notification_reminder_days',
                'value' => json_encode([-3, 0, 3]),
                'type' => 'json',
                'description' => 'Hari-hari pengingat notifikasi',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            // General
            [
                'id' => 'K2-CFG-01HQ000000000000004',
                'key' => 'upload_max_size_kb',
                'value' => '5120',
                'type' => 'number',
                'description' => 'Maks ukuran upload bukti transfer (KB)',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000005',
                'key' => 'upload_allowed_types',
                'value' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
                'type' => 'json',
                'description' => 'Tipe file yang diizinkan untuk upload',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000006',
                'key' => 'company_name',
                'value' => 'K2-Net',
                'type' => 'string',
                'description' => 'Nama perusahaan/brand',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000007',
                'key' => 'company_address',
                'value' => '',
                'type' => 'string',
                'description' => 'Alamat perusahaan',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000008',
                'key' => 'company_phone',
                'value' => '',
                'type' => 'string',
                'description' => 'Nomor telepon perusahaan',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            // Notification
            [
                'id' => 'K2-CFG-01HQ000000000000009',
                'key' => 'whatsapp_api_url',
                'value' => '',
                'type' => 'string',
                'description' => 'URL API WhatsApp gateway',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000010',
                'key' => 'email_from_address',
                'value' => '',
                'type' => 'string',
                'description' => 'Alamat email pengirim notifikasi',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000011',
                'key' => 'email_from_name',
                'value' => 'K2-Net',
                'type' => 'string',
                'description' => 'Nama pengirim email',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            [
                'id' => 'K2-CFG-01HQ000000000000012',
                'key' => 'bank_account_info',
                'value' => json_encode([
                    ['bank' => 'Bank BCA', 'account_number' => '1234567890', 'account_name' => 'K2-Net'],
                ]),
                'type' => 'json',
                'description' => 'Info rekening bank untuk transfer',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfiguration::updateOrCreate(
                ['id' => $config['id']],
                $config
            );
        }
    }
}
