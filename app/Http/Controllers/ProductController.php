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
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('alert_only')) {
            $query->belowReorderPoint();
        }

        $sortBy = $request->input('sort', 'stock_asc');
        switch ($sortBy) {
            case 'stock_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
        }

        $products = $query->paginate(20);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'            => ['required', 'string', 'max:100', 'unique:products'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_point'  => ['required', 'integer', 'min:0'],
        ]);
        Product::create($validated);
        return redirect()->route('products.index')->with('success', '商品を登録しました');
    }

    public function show(Product $product)
    {
        $transactions = $product->stockTransactions()->latest()->paginate(50);
        return view('products.show', compact('product', 'transactions'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku'           => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'reorder_point' => ['required', 'integer', 'min:0'],
        ]);
        $product->update($validated);
        return redirect()->route('products.index')->with('success', '商品情報を更新しました');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', '商品を削除しました');
    }

    public function qrcode(Product $product)
    {
        $url = route('products.show', $product);
        $svg = QrCode::format('svg')->size(200)->errorCorrection('M')->generate($url);
        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function qrcodeDownload(Product $product)
    {
        $url = route('products.show', $product);
        $png = QrCode::format('png')->size(300)->errorCorrection('M')->generate($url);
        $filename = 'qrcode_' . $product->sku . '.png';
        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function qrcodeSvgDownload(Product $product)
    {
        $url = route('products.show', $product);
        $svg = QrCode::format('svg')->size(300)->errorCorrection('M')->generate($url);
        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="qrcode_' . $product->sku . '.svg"');
    }

    public function label(Product $product)
    {
        return view('products.qr-label', compact('product'));
    }

    public function qrAll()
    {
        $products = Product::orderBy('name')->get();
        return view('products.qr-all', compact('products'));
    }

    public function reorderList()
    {
        $products = Product::belowReorderPoint()
            ->orderBy('stock_quantity', 'asc')
            ->get()
            ->map(function ($p) {
                $p->order_quantity = max(0, $p->reorder_point * 2 - $p->stock_quantity);
                return $p;
            });
        return view('products.reorder-list', compact('products'));
    }

    public function duplicate(Product $product)
    {
        $copy = $product->replicate();
        $copy->sku  = $product->sku . '-copy-' . substr(uniqid(), -4);
        $copy->name = $product->name . '（コピー）';
        $copy->stock_quantity = 0;
        $copy->save();
        return redirect()->route('products.edit', $copy)
            ->with('success', '商品を複製しました。内容を確認して保存してください。');
    }

    public function suggest(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 1) {
            return response()->json([]);
        }
        $products = Product::where('sku', 'like', "%{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->orderBy('name')->limit(10)->get(['id', 'sku', 'name']);
        return response()->json($products);
    }

    public function apiSearch(Request $request)
    {
        $query = Product::query();
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sq) use ($q) {
                $sq->where('sku', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%");
            });
        }
        if ($request->boolean('alert_only')) {
            $query->belowReorderPoint();
        }
        $products = $query->orderBy('name')->limit(100)->get(['id', 'sku', 'name', 'stock_quantity', 'reorder_point']);
        return response()->json(['data' => $products, 'total' => $products->count()]);
    }

    public function apiLowStock()
    {
        $products = Product::belowReorderPoint()
            ->orderBy('stock_quantity', 'asc')
            ->get(['id', 'sku', 'name', 'stock_quantity', 'reorder_point']);
        return response()->json(['data' => $products, 'total' => $products->count()]);
    }
}
