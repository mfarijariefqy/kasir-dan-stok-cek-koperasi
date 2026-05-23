<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::when($request->q, fn($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:units,name',
        ]);

        Unit::create([
            'name'      => strtolower(trim($request->name)),
            'is_active' => true,
        ]);

        return redirect()->route('units.index')
            ->with('success', 'Satuan berhasil ditambahkan');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name'      => 'required|string|max:50|unique:units,name,' . $unit->id,
            'is_active' => 'boolean',
        ]);

        $oldName = $unit->name;
        $newName = strtolower(trim($request->name));

        $unit->update([
            'name'      => $newName,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Propagate name change to products that use the old unit name
        if ($oldName !== $newName) {
            Product::where('unit', $oldName)->update(['unit' => $newName]);
        }

        return redirect()->route('units.index')
            ->with('success', 'Satuan berhasil diperbarui');
    }

    public function destroy(Unit $unit)
    {
        $inUse = Product::where('unit', $unit->name)->exists();

        if ($inUse) {
            return redirect()->route('units.index')
                ->with('error', "Satuan \"{$unit->name}\" tidak dapat dihapus karena masih digunakan oleh produk.");
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Satuan berhasil dihapus');
    }
}
