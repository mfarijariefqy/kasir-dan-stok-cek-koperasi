<?php

namespace App\Http\Controllers;

use App\Exports\ProductImportTemplateExport;
use App\Imports\ProductsImport;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    private function branchScope($query)
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        return $query;
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'branch']);
        $this->branchScope($query);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('branch_id') && auth()->user()->isSuperAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $branches   = auth()->user()->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('products.index', compact('products', 'categories', 'branches'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $branches   = Branch::where('is_active', true)->orderBy('name')->get();
        $units      = Unit::active()->orderBy('name')->get();
        return view('products.create', compact('categories', 'branches', 'units'));
    }

    public function store(Request $request)
    {
        $user     = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->branch_id : $user->branch_id;

        $request->validate([
            'barcode'     => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'barcode')
                    ->where('branch_id', $branchId)
                    ->where('unit', $request->unit)
                    ->whereNotNull('barcode'),
            ],
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit'        => 'required|string|max:50|exists:units,name',
            'buy_price'   => 'required|numeric|min:0',
            'sell_price'  => 'required|numeric|min:0',
            'stock_qty'   => 'required|integer|min:0',
            'branch_id'   => 'nullable|exists:branches,id',
            'is_active'   => 'boolean',
        ]);

        Product::create([
            'barcode'     => $request->barcode ?: null,
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'unit'        => $request->unit,
            'buy_price'   => $request->buy_price,
            'sell_price'  => $request->sell_price,
            'stock_qty'   => $request->stock_qty,
            'branch_id'   => $branchId,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $branches   = Branch::where('is_active', true)->orderBy('name')->get();
        $units      = Unit::active()->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'branches', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $user     = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->branch_id : $product->branch_id;

        $request->validate([
            'barcode'     => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'barcode')
                    ->ignore($product->id)
                    ->where('branch_id', $branchId)
                    ->where('unit', $request->unit)
                    ->whereNotNull('barcode'),
            ],
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit'        => 'required|string|max:50|exists:units,name',
            'buy_price'   => 'required|numeric|min:0',
            'sell_price'  => 'required|numeric|min:0',
            'branch_id'   => 'nullable|exists:branches,id',
            'is_active'   => 'boolean',
        ]);

        $product->update([
            'barcode'     => $request->barcode ?: null,
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'unit'        => $request->unit,
            'buy_price'   => $request->buy_price,
            'sell_price'  => $request->sell_price,
            'branch_id'   => $branchId,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }

    public function importForm()
    {
        return view('products.import');
    }

    public function importTemplate()
    {
        return Excel::download(new ProductImportTemplateExport(), 'template-import-produk.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new ProductsImport(auth()->user());
        Excel::import($import, $request->file('file'));

        $count  = $import->getImportedCount();
        $errors = $import->getErrors();

        return redirect()->route('products.index')
            ->with('import_count', $count)
            ->with('import_errors', $errors);
    }

    public function search(Request $request)
    {
        $term     = $request->input('q', '');
        $branchId = $request->input('branch_id');

        $query = Product::active()->with('category');

        // Filter by explicitly passed branch_id, otherwise fall back to user scope
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $this->branchScope($query);
        }

        if ($request->filled('barcode')) {
            $query->where('barcode', $request->barcode);
        } elseif ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%");
            });
        }

        $products = $query->with('branch')->limit(10)->get(['id', 'name', 'barcode', 'sell_price', 'buy_price', 'unit', 'stock_qty', 'branch_id']);

        return response()->json($products->map(fn($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'barcode'     => $p->barcode,
            'sell_price'  => $p->sell_price,
            'buy_price'   => $p->buy_price,
            'unit'        => $p->unit,
            'stock_qty'   => $p->stock_qty,
            'branch_id'   => $p->branch_id,
            'branch_name' => $p->branch->name ?? '-',
        ]));
    }
}
