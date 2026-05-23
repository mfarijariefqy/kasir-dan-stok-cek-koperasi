<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('users')->latest()->paginate(10);
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:branches,name',
            'address'   => 'nullable|string|max:500',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil ditambahkan');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address'   => 'nullable|string|max:500',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil diperbarui');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->users()->exists()) {
            return redirect()->route('branches.index')
                ->with('error', 'Cabang tidak dapat dihapus karena masih memiliki user aktif');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil dihapus');
    }
}
