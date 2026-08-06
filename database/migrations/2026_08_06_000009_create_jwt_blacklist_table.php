<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jwt_blacklist', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('token_id', 255)->unique();
            $table->string('token_hash', 255)->unique();
            $table->string('revoked_by', 50)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('blacklisted_at')->useCurrent();

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jwt_blacklist');
    }
};
