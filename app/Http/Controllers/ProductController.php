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
            $query->where(fn($q) => $q->where('sku', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }
        if ($request->boolean('alert_only')) $query->belowReorderPoint();
        $sortBy = $request->input('sort', 'stock_asc');
        match ($sortBy) {
            'stock_desc' => $query->orderBy('stock_quantity', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            default      => $query->orderBy('stock_quantity', 'asc'),
        };
        $products = $query->paginate(20);
        return view('products.index', compact('products'));
    }

    public function create() { return view('products.create'); }

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

    public function edit(Product $product) { return view('products.edit', compact('product')); }

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

    public function qrcode(Product $product, Request $request)
    {
        $fgHex = ltrim($request->input('color', '000000'), '#');
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $fgHex)) {
            $fgHex = '000000';
        }
        $r = hexdec(substr($fgHex, 0, 2));
        $g = hexdec(substr($fgHex, 2, 2));
        $b = hexdec(substr($fgHex, 4, 2));

        $svg = QrCode::format('svg')
            ->size(200)
            ->errorCorrection('M')
            ->color($r, $g, $b)
            ->generate(route('products.show', $product));

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function qrcodeDownload(Product $product)
    {
        $png = QrCode::format('png')->size(300)->errorCorrection('M')->generate(route('products.show', $product));
        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qrcode_' . $product->sku . '.png"');
    }

    public function qrcodeSvgDownload(Product $product)
    {
        $svg = QrCode::format('svg')->size(300)->errorCorrection('M')->generate(route('products.show', $product));
        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="qrcode_' . $product->sku . '.svg"');
    }

    public function label(Product $product) { return view('products.qr-label', compact('product')); }

    public function reorderList()
    {
        $products = Product::belowReorderPoint()->orderBy('stock_quantity', 'asc')->get()
            ->map(fn($p) => tap($p, fn($p) => $p->order_quantity = max(0, $p->reorder_point * 2 - $p->stock_quantity)));
        return view('products.reorder-list', compact('products'));
    }

    public function duplicate(Product $product)
    {
        $copy = $product->replicate();
        $copy->sku  = $product->sku . '-copy-' . substr(uniqid(), -4);
        $copy->name = $product->name . '（コピー）';
        $copy->stock_quantity = 0;
        $copy->save();
        return redirect()->route('products.edit', $copy)->with('success', '商品を複製しました。内容を確認して保存してください。');
    }

    public function suggest(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 1) return response()->json([]);
        return response()->json(
            Product::where('sku', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")
                ->orderBy('name')->limit(10)->get(['id', 'sku', 'name'])
        );
    }

    public function apiSearch(Request $request)
    {
        $query = Product::query();
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn($sq) => $sq->where('sku', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
        }
        if ($request->boolean('alert_only')) $query->belowReorderPoint();
        $products = $query->orderBy('name')->limit(100)->get(['id', 'sku', 'name', 'stock_quantity', 'reorder_point']);
        return response()->json(['data' => $products, 'total' => $products->count()]);
    }

    public function apiLowStock()
    {
        $products = Product::belowReorderPoint()->orderBy('stock_quantity', 'asc')
            ->get(['id', 'sku', 'name', 'stock_quantity', 'reorder_point']);
        return response()->json(['data' => $products, 'total' => $products->count()]);
    }
}
