<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        return view('pages.packages.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        $recordsTotal = Package::count();

        $search = trim((string) $request->input('search.value', ''));

        $query = Package::query()
            ->select('packages.*')
            ->orderBy('packages.name', 'asc');

        if ($search !== '') {
            $query->whereRaw(
                'LOWER(packages.name) LIKE ? OR LOWER(packages.speed) LIKE ?',
                ['%' . strtolower($search) . '%', '%' . strtolower($search) . '%']
            );
        }

        $recordsFiltered = $query->count();

        $start  = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function (Package $p) {
            return [
                'id'              => $p->id,
                'name'            => $p->name,
                'speed'           => $p->speed,
                'price'           => (float) $p->price,
                'formatted_price' => $p->formattedPrice(),
                'description'     => $p->description,
                'is_active'       => (bool) $p->is_active,
                'customer_count'  => $p->customers()->count(),
                'actions'         => [
                    'edit_url'   => route('admin.api.packages.show', ['package' => $p->id]),
                    'delete_url' => route('admin.api.packages.destroy', ['package' => $p->id]),
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

    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = Package::create([
            'name'        => $request->name,
            'speed'       => $request->speed,
            'price'       => $request->price,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Paket berhasil ditambahkan.', 'package' => $package], 201);
    }

    public function show(Package $package): JsonResponse
    {
        return response()->json($package);
    }

    public function update(UpdatePackageRequest $request, Package $package): JsonResponse
    {
        $package->update([
            'name'        => $request->name,
            'speed'       => $request->speed,
            'price'       => $request->price,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Paket berhasil diperbarui.']);
    }

    public function destroy(Package $package): JsonResponse
    {
        if ($package->customers()->exists()) {
            return response()->json(['message' => 'Paket tidak dapat dihapus karena sudah digunakan oleh pelanggan.'], 422);
        }

        $package->delete();

        return response()->json(['message' => 'Paket berhasil dihapus.']);
    }
}
