<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('invoice_id', 50)->unique();
            $table->string('customer_id', 50);
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->integer('file_size')->nullable();
            $table->string('file_type', 20);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
