<?php

namespace App\Events;

use App\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ============================================================
 * InvoiceCreated Event
 * ============================================================
 *
 * Dipanggil setiap kali invoice BARU berhasil dibuat.
 * Bisa dari:
 *   - Admin manual generate di /admin/invoices
 *   - Command auto-generate (AutoGenerateInvoiceCommand)
 *   - Cron bulanan
 *
 * Penggunaan di Controller/Command:
 *   event(new InvoiceCreated($invoice));
 *
 * Listener akan otomatis mengirim notifikasi WhatsApp
 * via WhatsAppService.
 */
class InvoiceCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }
}
