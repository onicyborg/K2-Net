<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.notifications.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        $recordsTotal = NotificationLog::count();

        $search = trim((string) $request->input('search.value', ''));

        $query = NotificationLog::with(['invoice', 'customer'])
            ->select('notification_logs.*')
            ->orderBy('notification_logs.created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', fn ($cq) => $cq->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']));
            });
        }

        $recordsFiltered = $query->count();

        $start  = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function (NotificationLog $log) {
            return [
                'id'                => $log->id,
                'customer_name'     => $log->customer?->name,
                'customer_code'     => $log->customer?->code,
                'invoice_number'    => $log->invoice?->invoice_number,
                'type'            => $log->notification_type,
                'type_label'      => $log->typeLabel(),
                'channel'         => $log->channel,
                'channel_label'   => ucfirst($log->channel),
                'status'          => $log->status,
                'status_label'    => $log->statusBadge()['label'],
                'status_class'    => $log->statusBadge()['class'],
                'sent_at'         => $log->sent_at?->format('d M Y H:i'),
                'failed_at'       => $log->failed_at?->format('d M Y H:i'),
                'error_message'   => $log->error_message,
                'meta'            => $log->meta,
                'created_at'      => $log->created_at->format('d M Y H:i'),
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}
