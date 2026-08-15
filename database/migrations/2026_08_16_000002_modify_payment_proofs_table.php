<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->change();
        });

        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropUnique('payment_proofs_invoice_id_unique');
            $table->uuid('invoice_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->uuid('invoice_id')->nullable(false)->change();
            $table->uuid('customer_id')->nullable(false)->change();
        });

        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->unique('invoice_id', 'payment_proofs_invoice_id_unique');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }
};
