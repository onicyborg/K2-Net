<?php

namespace Database\Seeders;

use App\Models\SystemConfiguration;
use Illuminate\Database\Seeder;

class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // General
            [
                'key' => 'company_name',
                'value' => 'K2-Net',
                'type' => 'string',
                'description' => 'Nama Perusahaan',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'key' => 'company_address',
                'value' => '',
                'type' => 'string',
                'description' => 'Alamat Perusahaan',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            [
                'key' => 'company_phone',
                'value' => '',
                'type' => 'string',
                'description' => 'Nomor Telepon',
                'group_name' => 'general',
                'is_editable' => true,
            ],
            // Billing
            [
                'key' => 'invoice_due_day',
                'value' => '10',
                'type' => 'number',
                'description' => 'Tanggal Jatuh Tempo (bulan berikutnya)',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
            [
                'key' => 'upload_max_size_kb',
                'value' => '5120',
                'type' => 'number',
                'description' => 'Maks Ukuran Upload Bukti Transfer (KB)',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
            [
                'key' => 'upload_allowed_types',
                'value' => json_encode(['pdf', 'jpg', 'jpeg', 'png']),
                'type' => 'json',
                'description' => 'Tipe File yang Dizinkan',
                'group_name' => 'billing',
                'is_editable' => true,
            ],
            [
                'key' => 'bank_account_info',
                'value' => json_encode([
                    ['bank' => 'Bank BCA', 'account_number' => '1234567890', 'account_name' => 'K2-Net'],
                ]),
                'type' => 'json',
                'description' => 'Info Rekening Bank',
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

        // Remove configs that are no longer needed
        $removeKeys = [
            'invoice_generate_day',
            'notification_reminder_days',
            'whatsapp_api_url',
            'email_from_address',
            'email_from_name',
        ];
        SystemConfiguration::whereIn('key', $removeKeys)->delete();
    }
}
