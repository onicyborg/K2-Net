<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->string('status', 30)->default('menunggu_verifikasi');
            $table->string('bank', 50);
            $table->string('account_number', 50);
            $table->string('account_name', 100);
            $table->bigInteger('transfer_amount');
            $table->string('transfer_from', 255);
            $table->date('transfer_date');
            $table->uuid('payment_proof_id')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->index('customer_id');
            $table->index('status');
            $table->index('submitted_at');
        });

        Schema::create('payment_submission_invoices', function (Blueprint $table) {
            $table->uuid('payment_submission_id');
            $table->uuid('invoice_id');

            $table->primary(['payment_submission_id', 'invoice_id']);
            $table->foreign('payment_submission_id')->references('id')->on('payment_submissions')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submission_invoices');
        Schema::dropIfExists('payment_submissions');
    }
};
