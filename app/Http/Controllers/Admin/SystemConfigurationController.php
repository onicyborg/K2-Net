<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemConfigurationController extends Controller
{
    public function index()
    {
        return view('pages.configurations.index');
    }

    public function datatable(): JsonResponse
    {
        $groups = SystemConfiguration::select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name');

        $configs = SystemConfiguration::orderBy('group_name')
            ->orderBy('description')
            ->get()
            ->groupBy('group_name');

        return response()->json([
            'groups' => $groups,
            'configs' => $configs,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $rules = [
            'key'   => 'required|string|max:100',
            'value' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $config = SystemConfiguration::where('key', $request->key)->first();

        if (!$config) {
            return response()->json(['message' => 'Konfigurasi tidak ditemukan.'], 404);
        }

        if (!$config->is_editable) {
            return response()->json(['message' => 'Konfigurasi ini tidak dapat diedit.'], 403);
        }

        $value = $request->value ?? '';

        if ($config->type === 'number') {
            $value = (string) (int) $request->value;
        } elseif ($config->type === 'boolean') {
            $value = $request->boolean('value') ? 'true' : 'false';
        } elseif ($config->type === 'json') {
            $decoded = json_decode($request->value, true);
            if ($request->value !== '' && $decoded === null) {
                return response()->json(['message' => 'Format JSON tidak valid.'], 422);
            }
            $value = $request->value;
        }

        $config->update(['value' => $value]);

        return response()->json([
            'message' => 'Konfigurasi berhasil disimpan.',
            'config'  => $config,
        ]);
    }
}
