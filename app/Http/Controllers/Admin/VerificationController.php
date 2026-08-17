<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\PaymentSubmission;
use App\Notifications\EmailTemplates;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.verifications.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        $recordsTotal = PaymentSubmission::count();

        $search = trim((string) $request->input('search.value', ''));

        $query = PaymentSubmission::query()
            ->with(['customer.package', 'paymentProof', 'invoices'])
            ->select('payment_submissions.*')
            ->where('payment_submissions.status', 'menunggu_verifikasi')
            ->orderBy('payment_submissions.submitted_at', 'desc');

        if ($search !== '') {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->whereRaw('LOWER(customers.name) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        $recordsFiltered = $query->count();

        $start  = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function (PaymentSubmission $sub) {
            return [
                'id'             => $sub->id,
                'customer_name'  => $sub->customer?->name,
                'customer_id'    => $sub->customer_id,
                'invoice_count'  => $sub->invoices->count(),
                'billing_periods'=> $sub->billingPeriods(),
                'amount'        => $sub->transfer_amount,
                'formatted_amount'=> $sub->formattedAmount(),
                'bank'           => $sub->bank,
                'account_number' => $sub->account_number,
                'account_name'  => $sub->account_name,
                'transfer_from' => $sub->transfer_from,
                'transfer_date'  => $sub->transfer_date->format('d M Y'),
                'submitted_at'   => $sub->submitted_at->format('d M Y H:i'),
                'actions'       => [
                    'show_url'    => route('admin.api.verifications.show', ['submission' => $sub->id]),
                    'approve_url' => route('admin.api.verifications.approve', ['submission' => $sub->id]),
                    'reject_url'  => route('admin.api.verifications.reject', ['submission' => $sub->id]),
                ],
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function show(PaymentSubmission $submission): JsonResponse
    {
        $submission->load(['customer.package', 'paymentProof', 'invoices']);

        // Generate presigned URL for payment proof (expires in 1 hour)
        if ($submission->paymentProof && $submission->paymentProof->file_path) {
            $submission->paymentProof->file_url = Storage::disk('r2')->temporaryUrl(
                $submission->paymentProof->file_path,
                now()->addHour()
            );
        }

        $submission->formatted_amount = $submission->formattedAmount();
        $submission->billing_periods = $submission->billingPeriods();
        $submission->actions = [
            'approve_url' => route('admin.api.verifications.approve', ['submission' => $submission->id]),
            'reject_url'  => route('admin.api.verifications.reject', ['submission' => $submission->id]),
        ];
        return response()->json($submission);
    }

    public function approve(Request $request, PaymentSubmission $submission): JsonResponse
    {
        if ($submission->status !== 'menunggu_verifikasi') {
            return response()->json(['message' => 'Submission tidak dalam status menunggu verifikasi.'], 422);
        }

        DB::beginTransaction();
        try {
            $submission->update(['status' => 'disetujui']);

            foreach ($submission->invoices as $invoice) {
                NotificationLog::create([
                    'invoice_id'         => $invoice->id,
                    'customer_id'        => $submission->customer_id,
                    'notification_type'  => 'confirmation',
                    'channel'           => 'email',
                    'status'            => 'sent',
                    'sent_at'           => now(),
                    'meta'              => [
                        'submission_id'  => $submission->id,
                        'invoice_number' => $invoice->invoice_number,
                        'amount'        => $invoice->formattedAmount(),
                    ],
                ]);
                $invoice->update([
                    'status'  => 'lunas',
                    'paid_at' => now(),
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyetujui submission.'], 500);
        }

        $this->sendNotification($submission, 'confirmation');

        return response()->json(['message' => 'Submission berhasil disetujui dan semua tagihan ditandai lunas.']);
    }

    public function reject(Request $request, PaymentSubmission $submission): JsonResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        if ($submission->status !== 'menunggu_verifikasi') {
            return response()->json(['message' => 'Submission tidak dalam status menunggu verifikasi.'], 422);
        }

        DB::beginTransaction();
        try {
            $submission->update([
                'status'           => 'ditolak',
                'rejection_reason' => $request->rejection_reason,
            ]);

            foreach ($submission->invoices as $invoice) {
                $invoice->update([
                    'status'           => 'ditolak',
                    'rejection_reason' => $request->rejection_reason,
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menolak submission.'], 500);
        }

        $this->sendNotification($submission, 'rejection');

        return response()->json(['message' => 'Submission berhasil ditolak.']);
    }

    private function sendNotification(PaymentSubmission $submission, string $type): void
    {
        $customer = $submission->customer;
        $user = $customer->user;
        $portalUrl = $customer->getPortalUrl();
        $invoiceCount = $submission->invoices->count();
        $periodsText = $submission->billingPeriods();

        if (!$user?->email) {
            return;
        }

        try {
            if ($type === 'confirmation') {
                Mail::send([], [], function ($message) use ($user, $submission, $invoiceCount, $periodsText) {
                    $logoCid = EmailTemplates::attachLogo($message);

                    $content = EmailTemplates::greeting($user->name);
                    $content .= EmailTemplates::paragraph('Pembayaran Anda telah <strong>diterima dan dikonfirmasi</strong>. Terima kasih atas pembayarannya.');
                    $content .= EmailTemplates::paymentDetailTable([
                        ['label' => 'Jumlah Tagihan', 'value' => $invoiceCount . ' tagihan'],
                        ['label' => 'Periode', 'value' => $periodsText],
                        ['label' => 'Jumlah Transfer', 'value' => $submission->formattedAmount()],
                        ['label' => 'Tanggal Transfer', 'value' => $submission->transfer_date->format('d F Y')],
                    ]);
                    $content .= EmailTemplates::alert('Semua tagihan lunas. Terima kasih!', 'success', '#16a34a');

                    $message->to($user->email, $user->name)
                        ->subject("Pembayaran Diterima — {$invoiceCount} Tagihan")
                        ->html(EmailTemplates::wrapper(
                            $content,
                            'Pembayaran Diterima',
                            'K2-Net — Sistem Manajemen Tagihan & Pelanggan',
                            '#16a34a',
                            $logoCid
                        ));
                });
            } elseif ($type === 'rejection') {
                Mail::send([], [], function ($message) use ($user, $submission, $invoiceCount, $periodsText) {
                    $logoCid = EmailTemplates::attachLogo($message);

                    $content = EmailTemplates::greeting($user->name);
                    $content .= EmailTemplates::paragraph('Mohon maaf, pembayaran Anda telah <strong>ditolak</strong> dengan alasan:');
                    $content .= EmailTemplates::alert($submission->rejection_reason, 'danger', '#dc2626');
                    $content .= EmailTemplates::paymentDetailTable([
                        ['label' => 'Jumlah Tagihan', 'value' => $invoiceCount . ' tagihan'],
                        ['label' => 'Periode', 'value' => $periodsText],
                        ['label' => 'Jumlah Transfer', 'value' => $submission->formattedAmount()],
                    ]);
                    $content .= EmailTemplates::paragraph('Silakan upload bukti pembayaran yang benar melalui tautan berikut:');
                    $content .= EmailTemplates::ctaButton($submission->customer->getPortalUrl(), 'Bayar Ulang', '#dc2626');
                    $content .= EmailTemplates::fallbackLink($submission->customer->getPortalUrl());

                    $message->to($user->email, $user->name)
                        ->subject("Pembayaran Ditolak — {$invoiceCount} Tagihan")
                        ->html(EmailTemplates::wrapper(
                            $content,
                            'Pembayaran Ditolak',
                            'K2-Net — Sistem Manajemen Tagihan & Pelanggan',
                            '#dc2626',
                            $logoCid
                        ));
                });
            }
        } catch (\Throwable $e) {
            Log::error('[VerificationController] Gagal kirim email: ' . $e->getMessage());
        }
    }
}
