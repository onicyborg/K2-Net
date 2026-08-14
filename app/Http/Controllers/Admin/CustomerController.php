<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $packages = Package::active()->orderBy('name')->get();
        return view('pages.customers.index', compact('packages'));
    }

    public function datatable(Request $request): JsonResponse
    {
        $recordsTotal = Customer::count();

        $search = trim((string) $request->input('search.value', ''));

        $query = Customer::query()
            ->with('package')
            ->select('customers.*')
            ->orderBy('customers.created_at', 'desc');

        if ($search !== '') {
            $query->whereRaw(
                'LOWER(customers.name) LIKE ? OR LOWER(customers.email) LIKE ? OR LOWER(customers.whatsapp_number) LIKE ?',
                ['%' . strtolower($search) . '%', '%' . strtolower($search) . '%', '%' . strtolower($search) . '%']
            );
        }

        $recordsFiltered = $query->count();

        $start  = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));

        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function (Customer $c) {
            return [
                'id'           => $c->id,
                'name'         => $c->name,
                'email'        => $c->email,
                'phone'        => $c->whatsapp_number,
                'package_name' => $c->package?->name,
                'package_id'   => $c->package_id,
                'status'       => $c->status,
                'status_badge' => $c->statusBadge(),
                'created_at'   => $c->created_at->format('d M Y'),
                'actions'      => [
                    'edit_url'   => route('admin.api.customers.show', ['customer' => $c->id]),
                    'delete_url' => route('admin.api.customers.destroy', ['customer' => $c->id]),
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

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create([
            'user_id'            => Auth::id(),
            'code'               => 'CUST-' . strtoupper(uniqid()),
            'name'               => $request->name,
            'email'              => $request->email,
            'whatsapp_number'    => $request->whatsapp_number,
            'whatsapp_number_full' => $request->whatsapp_number_full,
            'address'            => $request->address,
            'package_id'         => $request->package_id,
            'status'             => $request->status,
            'notes'              => $request->notes,
        ]);

        return response()->json(['message' => 'Pelanggan berhasil ditambahkan.', 'customer' => $customer], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load('package');
        return response()->json($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return response()->json(['message' => 'Pelanggan berhasil diperbarui.']);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Pelanggan berhasil dihapus.']);
    }
}
