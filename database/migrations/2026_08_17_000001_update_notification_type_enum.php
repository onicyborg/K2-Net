<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $allowed = [
            'reminder_h3',
            'reminder_before',
            'reminder_overdue',
            'confirmation',
            'rejection',
            'billing_reminder_active',
            'billing_reminder_due',
        ];

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // Postgres: drop & recreate CHECK constraint
            DB::statement("ALTER TABLE notification_logs DROP CONSTRAINT IF EXISTS notification_logs_notification_type_check");

            $list = "'" . implode("','", $allowed) . "'";
            DB::statement("ALTER TABLE notification_logs ADD CONSTRAINT notification_logs_notification_type_check CHECK (notification_type IN ({$list}))");
        } else {
            // MySQL / MariaDB: recreate the column with new enum definition
            $list = "'" . implode("','", $allowed) . "'";

            // Drop foreign keys temporarily (mysql only) — actually not needed for column modification
            // Just modify column type
            DB::statement("ALTER TABLE notification_logs MODIFY COLUMN notification_type ENUM($list) NOT NULL");
        }
    }

    public function down(): void
    {
        $original = [
            'reminder_h3',
            'reminder_h0',
            'reminder_h3_late',
            'confirmation',
            'rejection',
        ];

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE notification_logs DROP CONSTRAINT IF EXISTS notification_logs_notification_type_check");

            $list = "'" . implode("','", $original) . "'";
            DB::statement("ALTER TABLE notification_logs ADD CONSTRAINT notification_logs_notification_type_check CHECK (notification_type IN ({$list}))");
        } else {
            $list = "'" . implode("','", $original) . "'";
            DB::statement("ALTER TABLE notification_logs MODIFY COLUMN notification_type ENUM($list) NOT NULL");
        }
    }
};
