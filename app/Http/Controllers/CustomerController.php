<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with('user')
            ->when($request->q,
                fn($q) => $q->where('name', 'like', '%' . $request->q . '%')
                             ->orWhere('phone', 'like', '%' . $request->q . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:500',
        ];

        if ($request->boolean('create_account')) {
            $rules['username'] = ['required', 'string', 'min:4', 'max:50', 'alpha_num', 'unique:users,username'];
            $rules['password'] = ['required', 'string', 'min:6', 'confirmed'];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request) {
            $customer = Customer::create(
                $request->only('name', 'phone', 'address', 'notes') + ['is_active' => true]
            );

            if ($request->boolean('create_account')) {
                $user = User::create([
                    'name'     => $customer->name,
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                ]);
                $user->assignRole('pelanggan');
                $customer->update(['user_id' => $user->id]);
            }
        });

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan');
    }

    public function edit(Customer $customer)
    {
        $customer->load('user');
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $rules = [
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:500',
        ];

        $action = $request->input('account_action', 'none'); // none | create | update | remove

        if ($action === 'create') {
            $rules['username'] = ['required', 'string', 'min:4', 'max:50', 'alpha_num', 'unique:users,username'];
            $rules['password'] = ['required', 'string', 'min:6', 'confirmed'];
        } elseif ($action === 'update') {
            $rules['username'] = ['required', 'string', 'min:4', 'max:50', 'alpha_num',
                Rule::unique('users', 'username')->ignore($customer->user_id)];
            $rules['password'] = ['nullable', 'string', 'min:6', 'confirmed'];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $customer, $action) {
            $customer->update(
                $request->only('name', 'phone', 'address', 'notes') + [
                    'is_active' => $request->boolean('is_active'),
                ]
            );

            if ($action === 'create') {
                $user = User::create([
                    'name'     => $customer->name,
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                ]);
                $user->assignRole('pelanggan');
                $customer->update(['user_id' => $user->id]);

            } elseif ($action === 'update' && $customer->user) {
                $userData = ['name' => $customer->name, 'username' => $request->username];
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $customer->user->update($userData);

            } elseif ($action === 'remove' && $customer->user) {
                $user = $customer->user;
                $customer->update(['user_id' => null]);
                $user->delete();
            }
        });

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan berhasil diperbarui');
    }

    public function destroy(Customer $customer)
    {
        DB::transaction(function () use ($customer) {
            if ($customer->user) {
                $user = $customer->user;
                $customer->update(['user_id' => null]);
                $user->delete();
            }
            $customer->delete();
        });

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus');
    }

    public function search(Request $request)
    {
        $customers = Customer::active()
            ->when($request->q, fn($q) => $q->where('name', 'like', '%' . $request->q . '%')
                                            ->orWhere('phone', 'like', '%' . $request->q . '%'))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'phone']);

        return response()->json($customers);
    }
}
