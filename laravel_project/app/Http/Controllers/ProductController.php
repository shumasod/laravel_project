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
    /**
     * 在庫一覧表示
     */
    public function index(Request $request)
    {
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
        return view('products.create');
    }

    /**
     * 商品登録処理
     */
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

        return redirect()
            ->route('products.index')
            ->with('success', '商品を登録しました');
    }

    /**
     * 商品詳細（在庫履歴表示）
     */
    public function show(Product $product)
    {
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
        return view('products.edit', compact('product'));
    }

    /**
     * 商品更新処理
     */
    public function update(Request $request, Product $product)
    {
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

    public function export()
    {
        $products = Product::orderBy('name')->get();
        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
        $csv = $bom . "SKU,商品名,説明,在庫数,発注点,ステータス,登録日\n";
        foreach ($products as $p) {
            $status = $p->isOutOfStock() ? '在庫切れ' : ($p->isBelowReorderPoint() ? '要発注' : '正常');
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $p->sku) . '"',
                '"' . str_replace('"', '""', $p->name) . '"',
                '"' . str_replace('"', '""', $p->description ?? '') . '"',
                $p->stock_quantity,
                $p->reorder_point,
                '"' . $status . '"',
                $p->created_at->format('Y-m-d'),
            ]) . "\n";
        }
        $filename = 'products_' . date('Ymd') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
