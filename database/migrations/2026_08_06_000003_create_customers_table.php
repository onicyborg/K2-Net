<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('whatsapp_number', 20);
            $table->string('whatsapp_number_full', 25)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address');
            $table->uuid('package_id');
            $table->enum('status', ['aktif', 'isolir', 'nonaktif'])->default('aktif');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('restrict');

            $table->index('status');
            $table->index('package_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
