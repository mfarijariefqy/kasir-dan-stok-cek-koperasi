<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'branch'])->latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles    = Role::all();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6|confirmed',
            'role'      => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        $roles    = Role::all();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|min:6|confirmed',
            'role'      => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus user yang sedang login');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
