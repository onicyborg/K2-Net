<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * ============================================================
 * CronController
 * ============================================================
 *
 * Endpoint HTTP untuk trigger scheduled jobs via external cron
 * service (cron-job.org). Masing-masing endpoint terlindungi
 * token sederhana (?token=...). Tidak butuh login user.
 *
 * Cara pakai:
 *   GET https://yourdomain.com/cron/invoices-remind?token=XXX
 *   GET https://yourdomain.com/cron/invoices-auto-generate?token=XXX
 *   GET https://yourdomain.com/cron/billing-reminder-active?token=XXX
 *   GET https://yourdomain.com/cron/billing-reminder-due?token=XXX
 *
 * Token diset di .env sebagai CRON_TOKEN.
 */
class CronController extends Controller
{
    private function checkToken(Request $request): bool
    {
        $token = config('app.cron_token');

        // Jika CRON_TOKEN belum diset, tolak semua request.
        if (empty($token)) {
            return false;
        }

        return hash_equals((string) $token, (string) $request->query('token', ''));
    }

    private function run(string $command, array $args = []): JsonResponse
    {
        try {
            $exitCode = Artisan::call($command, $args);
            $output = Artisan::output();

            return response()->json([
                'status'    => $exitCode === 0 ? 'ok' : 'error',
                'command'   => $command,
                'exit_code' => $exitCode,
                'output'    => $output,
                'ran_at'    => now()->toDateTimeString(),
            ], $exitCode === 0 ? 200 : 500);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'command' => $command,
                'message' => $e->getMessage(),
                'ran_at'  => now()->toDateTimeString(),
            ], 500);
        }
    }

    public function invoicesRemind(Request $request): JsonResponse
    {
        if (!$this->checkToken($request)) {
            abort(403, 'Invalid token.');
        }
        return $this->run('invoices:remind');
    }

    public function invoicesAutoGenerate(Request $request): JsonResponse
    {
        if (!$this->checkToken($request)) {
            abort(403, 'Invalid token.');
        }
        return $this->run('invoices:auto-generate');
    }

    public function billingReminderActive(Request $request): JsonResponse
    {
        if (!$this->checkToken($request)) {
            abort(403, 'Invalid token.');
        }
        return $this->run('billing:send-reminders', ['--type' => 'active']);
    }

    public function billingReminderDue(Request $request): JsonResponse
    {
        if (!$this->checkToken($request)) {
            abort(403, 'Invalid token.');
        }
        return $this->run('billing:send-reminders', ['--type' => 'due']);
    }
}
