<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * 商品管理コントローラー
 *
 * WHY: Controllerは薄く保ち、ビジネスロジックはServiceに委譲
 */
class ProductController extends Controller
{
    /**
     * 在庫一覧表示
     */
    public function index(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'search'     => 'nullable|string|max:100',
            'sort'       => 'nullable|in:stock_asc,stock_desc,name',
            'alert_only' => 'nullable|boolean',
        ]);

        $query = Product::query();

        // 検索フィルタ
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // 在庫アラートフィルタ
        if ($request->boolean('alert_only')) {
            $query->belowReorderPoint();
        }

        // ソート（デフォルト: 在庫が少ない順）
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

    /**
     * 商品登録フォーム
     */
    public function create()
    {
        Gate::authorize('admin');

        return view('products.create');
    }

    /**
     * 商品登録処理
     */
    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_point' => ['required', 'integer', 'min:0'],
        ]);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', '商品を登録しました');
    }

    /**
     * 商品詳細（在庫履歴表示）
     */
    public function show(Product $product)
    {
        Gate::authorize('admin');

        $transactions = $product->stockTransactions()
            ->latest()
            ->paginate(50);

        return view('products.show', compact('product', 'transactions'));
    }

    /**
     * 商品更新フォーム
     */
    public function edit(Product $product)
    {
        Gate::authorize('admin');

        return view('products.edit', compact('product'));
    }

    /**
     * 商品更新処理
     */
    public function update(Request $request, Product $product)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reorder_point' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', '商品情報を更新しました');
    }

    /**
     * 商品削除
     */
    public function destroy(Product $product)
    {
        Gate::authorize('admin');

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', '商品を削除しました');
    }

    /**
     * QRコード画像を返す（SVG形式）
     * QRコードにはSKUと商品詳細URLをエンコード
     */
    public function qrcode(Product $product)
    {
        Gate::authorize('admin');

        $url = route('products.show', $product);
        $svg = QrCode::format('svg')
            ->size(200)
            ->errorCorrection('M')
            ->generate($url);

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml');
    }

    /**
     * QRコードをPNGとしてダウンロード
     */
    public function qrcodeDownload(Product $product)
    {
        Gate::authorize('admin');

        $url = route('products.show', $product);
        $png = QrCode::format('png')
            ->size(300)
            ->errorCorrection('M')
            ->generate($url);

        $filename = 'qrcode_' . $product->sku . '.png';

        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
