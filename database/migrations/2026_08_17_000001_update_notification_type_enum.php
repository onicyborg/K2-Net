<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL — ubah enum via ALTER TYPE
        DB::statement("ALTER TABLE notification_logs DROP CONSTRAINT notification_logs_notification_type_check");

        DB::statement("ALTER TABLE notification_logs ADD CONSTRAINT notification_logs_notification_type_check CHECK (notification_type IN (
            'reminder_h3',
            'reminder_before',
            'reminder_overdue',
            'confirmation',
            'rejection',
            'billing_reminder_active',
            'billing_reminder_due'
        ))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notification_logs DROP CONSTRAINT notification_logs_notification_type_check");

        DB::statement("ALTER TABLE notification_logs ADD CONSTRAINT notification_logs_notification_type_check CHECK (notification_type IN (
            'reminder_h3',
            'reminder_h0',
            'reminder_h3_late',
            'confirmation',
            'rejection'
        ))");
    }
};
