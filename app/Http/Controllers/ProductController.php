<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn ($q) => $q->where('sku', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }
        if ($request->boolean('alert_only')) { $query->belowReorderPoint(); }
        match ($request->input('sort', 'stock_asc')) {
            'stock_desc' => $query->orderBy('stock_quantity', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            default      => $query->orderBy('stock_quantity', 'asc'),
        };
        return view('products.index', ['products' => $query->paginate(20)]);
    }

    public function create() { return view('products.create'); }

    public function store(Request $request)
    {
        Product::create($request->validate([
            'sku' => ['required','string','max:100','unique:products'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'stock_quantity' => ['required','integer','min:0'],
            'reorder_point'  => ['required','integer','min:0'],
        ]));
        return redirect()->route('products.index')->with('success', '商品を登録しました');
    }

    public function show(Product $product)
    {
        return view('products.show', [
            'product' => $product,
            'transactions' => $product->stockTransactions()->latest()->paginate(50),
        ]);
    }

    public function edit(Product $product) { return view('products.edit', compact('product')); }

    public function update(Request $request, Product $product)
    {
        $product->update($request->validate([
            'sku' => ['required','string','max:100','unique:products,sku,'.$product->id],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'reorder_point' => ['required','integer','min:0'],
        ]));
        return redirect()->route('products.index')->with('success', '商品情報を更新しました');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', '商品を削除しました');
    }

    public function qrcode(Product $product)
    {
        $svg = QrCode::format('svg')->size(200)->errorCorrection('M')->generate(route('products.show', $product));
        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function qrcodeDownload(Product $product)
    {
        $png = QrCode::format('png')->size(300)->errorCorrection('M')->generate(route('products.show', $product));
        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qrcode_' . $product->sku . '.png"');
    }

    public function apiLowStock()
    {
        $products = Product::belowReorderPoint()
            ->orderBy('stock_quantity', 'asc')
            ->get(['id', 'sku', 'name', 'stock_quantity', 'reorder_point']);

        return response()->json([
            'data'  => $products,
            'total' => $products->count(),
        ]);
    }
}
