<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 100)->unique();
            $table->uuid('customer_id');
            $table->date('billing_period');
            $table->decimal('amount', 12, 0);
            $table->date('due_date');
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak'])->default('belum_bayar');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('issued_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');

            $table->index('customer_id');
            $table->index('status');
            $table->index('billing_period');
            $table->index('due_date');
            $table->unique(['customer_id', 'billing_period'], 'invoices_customer_billing_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
