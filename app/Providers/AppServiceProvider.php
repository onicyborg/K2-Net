<?php

namespace App\Providers;

use App\Events\InvoiceCreated;
use App\Listeners\SendInvoiceCreatedWhatsAppNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan listener event ke event-nya.
        // Laravel akan otomatis memanggil listener setiap kali
        // event di-dispatch (baik dari controller maupun command).
        //
        // Tambahkan listener lain di sini seiring bertambahnya
        // channel notifikasi (misal Telegram, SMS, dll).
        Event::listen(
            InvoiceCreated::class,
            SendInvoiceCreatedWhatsAppNotification::class,
        );
    }
}
