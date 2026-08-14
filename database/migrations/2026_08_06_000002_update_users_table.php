<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if id is still bigint (before UUID migration) — only run on existing data
        $isBigint = DB::selectOne("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'id' AND data_type = 'bigint'");

        if ($isBigint) {
            Schema::table('users', function (Blueprint $table) {
                DB::statement('ALTER TABLE "users" DROP CONSTRAINT "users_pkey"');
                DB::statement('ALTER TABLE "users" ALTER COLUMN "id" TYPE uuid USING gen_random_uuid()');
                DB::statement('ALTER TABLE "users" ALTER COLUMN "id" SET NOT NULL');
                DB::statement('ALTER TABLE "users" ADD PRIMARY KEY ("id")');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'pelanggan'])->default('pelanggan')->after('password');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role']);
        });
    }
};
