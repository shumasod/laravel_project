<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * 商品管理コントローラー
 *
 * WHY: Controllerは薄く保ち、ビジネスロジックはServiceに委譲
 */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
            });
        }
        if ($request->boolean('alert_only')) { $query->belowReorderPoint(); }
        $sortBy = $request->input('sort', 'stock_asc');
        switch ($sortBy) {
            case 'stock_asc': $query->orderBy('stock_quantity', 'asc'); break;
            case 'stock_desc': $query->orderBy('stock_quantity', 'desc'); break;
            case 'name': $query->orderBy('name', 'asc'); break;
        }
        $products = $query->paginate(20);
        return view('products.index', compact('products'));
    }

    public function create() { return view('products.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_point' => ['required', 'integer', 'min:0'],
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
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
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

    /**
     * QRコード画像を返す（SVG形式）
     * QRコードには公開スキャンURLをエンコードし認証なしでアクセス可能にする
     */
    public function qrcode(Product $product)
    {
        $url = url("/scan/{$product->sku}");
        $svg = QrCode::format('svg')->size(200)->errorCorrection('M')->generate($url);
        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function qrcodeDownload(Product $product)
    {
        $url = url("/scan/{$product->sku}");
        $png = QrCode::format('png')->size(300)->errorCorrection('M')->generate($url);
        $filename = 'qrcode_' . $product->sku . '.png';
        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Public scan endpoint — looks up product by SKU and redirects to detail page.
     * Encoded in QR codes so they can be scanned without authentication.
     */
    public function scanRedirect(string $sku)
    {
        $product = Product::where('sku', $sku)->first();
        if (!$product) {
            return redirect('/')->with('error', "SKU「{$sku}」の商品が見つかりませんでした");
        }
        return redirect()->route('products.show', $product);
    }
}
