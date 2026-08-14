<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\SystemConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PortalController extends Controller
{
    public function showPaymentPage(string $code)
    {
        $customer = Customer::where('portal_code', $code)->first();

        if (!$customer) {
            return response()->view('pages.portal.invalid', [], 404);
        }

        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['belum_bayar', 'ditolak'])
            ->with('customer.package')
            ->orderBy('billing_period', 'asc')
            ->get();

        $bankAccounts = SystemConfiguration::getValue('bank_account_info', []);
        $companyName = SystemConfiguration::getValue('company_name', 'K2-Net');

        return view('pages.portal.index', [
            'customer'    => $customer,
            'invoices'    => $invoices,
            'bankAccounts' => $bankAccounts,
            'companyName' => $companyName,
        ]);
    }

    public function submitPayment(Request $request, string $code): JsonResponse
    {
        $customer = Customer::where('portal_code', $code)->first();

        if (!$customer) {
            return response()->json(['message' => 'Kode tidak valid.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'invoice_ids'   => 'required|string',
            'transfer_to'   => 'required|string|max:255',
            'transfer_from' => 'required|string|max:255',
            'transfer_amount' => 'required|numeric|min:1000',
            'transfer_date' => 'required|date',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'invoice_ids.required'   => 'Pilih minimal satu tagihan.',
            'payment_proof.required' => 'Bukti transfer wajib diupload.',
            'payment_proof.mimes'   => 'Format file harus JPG, PNG, atau PDF.',
            'payment_proof.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $invoiceIds = array_filter(array_map('trim', explode(',', $request->invoice_ids)));

        if (empty($invoiceIds)) {
            return response()->json(['message' => 'Pilih minimal satu tagihan.'], 422);
        }

        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['belum_bayar', 'ditolak'])
            ->get();

        if ($invoices->isEmpty()) {
            return response()->json(['message' => 'Tidak ada tagihan yang valid untuk dibayar.'], 422);
        }

        $paymentProofFile = $request->file('payment_proof');
        $fileName = $paymentProofFile->getClientOriginalName();
        $filePath = $paymentProofFile->store('payment-proofs', 'public');
        $fileSize = $paymentProofFile->getSize();
        $fileType = strtolower($paymentProofFile->getClientOriginalExtension());

        $uploadedBy = $customer->user_id;

        foreach ($invoices as $invoice) {
            PaymentProof::updateOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'customer_id'   => $customer->id,
                    'file_name'     => $fileName,
                    'file_path'     => $filePath,
                    'file_size'     => $fileSize,
                    'file_type'     => $fileType,
                    'uploaded_at'   => now(),
                ]
            );

            $invoice->update([
                'status'         => 'menunggu_verifikasi',
                'rejection_reason' => null,
            ]);
        }

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diupload. Tim kami akan segera memverifikasi.',
            'invoice_count' => $invoices->count(),
        ], 201);
    }
}
