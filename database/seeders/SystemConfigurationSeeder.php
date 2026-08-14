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
                'key' => 'invoice_generate_day',
                'value' => '25',
                'type' => 'number',
                'description' => 'Tanggal generate invoice bulanan',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
            [
                'key' => 'invoice_due_day',
                'value' => '5',
                'type' => 'number',
                'description' => 'Tanggal jatuh tempo (bulan berikutnya)',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
            [
                'key' => 'notification_reminder_days',
                'value' => json_encode([-3, 0, 3]),
                'type' => 'json',
                'description' => 'Hari-hari pengingat notifikasi',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            // General
            [
                'key' => 'upload_max_size_kb',
                'value' => '5120',
                'type' => 'number',
                'description' => 'Maks ukuran upload bukti transfer (KB)',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'key' => 'upload_allowed_types',
                'value' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
                'type' => 'json',
                'description' => 'Tipe file yang diizinkan untuk upload',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'key' => 'company_name',
                'value' => 'K2-Net',
                'type' => 'string',
                'description' => 'Nama perusahaan/brand',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'key' => 'company_address',
                'value' => '',
                'type' => 'string',
                'description' => 'Alamat perusahaan',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'key' => 'company_phone',
                'value' => '',
                'type' => 'string',
                'description' => 'Nomor telepon perusahaan',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            // Notification
            [
                'key' => 'whatsapp_api_url',
                'value' => '',
                'type' => 'string',
                'description' => 'URL API WhatsApp gateway',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            [
                'key' => 'email_from_address',
                'value' => '',
                'type' => 'string',
                'description' => 'Alamat email pengirim notifikasi',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            [
                'key' => 'email_from_name',
                'value' => 'K2-Net',
                'type' => 'string',
                'description' => 'Nama pengirim email',
                'group_name' => 'notification',
                'is_editable' => true,
            ],
            [
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
                ['key' => $config['key']],
                $config
            );
        }
    }
}
