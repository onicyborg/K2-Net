<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\PaymentSubmission;
use App\Models\SystemConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            'invoice_ids'      => 'required|string',
            'transfer_to'      => 'required|string|max:255',
            'transfer_from'    => 'required|string|max:255',
            'transfer_amount'  => 'required|numeric|min:1000',
            'transfer_date'    => 'required|date',
            'payment_proof'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'invoice_ids.required'   => 'Pilih minimal satu tagihan.',
            'payment_proof.required' => 'Bukti transfer wajib diupload.',
            'payment_proof.mimes'   => 'Format file harus JPG, PNG, atau PDF.',
            'payment_proof.max'     => 'Ukuran file maksimal 5MB.',
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
        $filePath = $paymentProofFile->store('payment-proofs', 'r2');
        $fileSize = $paymentProofFile->getSize();
        $fileType = strtolower($paymentProofFile->getClientOriginalExtension());

        $transferParts = explode('|', $request->transfer_to);
        $bank = $transferParts[0] ?? '';
        $accountNumber = $transferParts[1] ?? '';
        $accountName = $transferParts[2] ?? '';

        DB::beginTransaction();
        try {
            $paymentProof = PaymentProof::create([
                'customer_id'  => $customer->id,
                'file_name'   => $fileName,
                'file_path'   => $filePath,
                'file_size'   => $fileSize,
                'file_type'   => $fileType,
                'uploaded_at' => now(),
            ]);

            $submission = PaymentSubmission::create([
                'customer_id'      => $customer->id,
                'status'           => 'menunggu_verifikasi',
                'bank'             => $bank,
                'account_number'   => $accountNumber,
                'account_name'    => $accountName,
                'transfer_amount'  => (int) $request->transfer_amount,
                'transfer_from'   => $request->transfer_from,
                'transfer_date'    => $request->transfer_date,
                'payment_proof_id' => $paymentProof->id,
                'submitted_at'     => now(),
            ]);

            $submission->invoices()->attach($invoices->pluck('id'));

            foreach ($invoices as $invoice) {
                $invoice->update([
                    'status'           => 'menunggu_verifikasi',
                    'rejection_reason' => null,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan submission.'], 500);
        }

        return response()->json([
            'message'       => 'Bukti pembayaran berhasil diupload. Tim kami akan segera memverifikasi.',
            'invoice_count' => $invoices->count(),
            'submission_id' => $submission->id,
        ], 201);
    }
}
