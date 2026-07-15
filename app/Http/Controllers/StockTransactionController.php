<?php

namespace App\Http\Controllers;

use App\Enums\StockTransactionType;
use App\Http\Requests\StockOperationRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StockTransactionController extends Controller
{
    public function __construct(
        private StockService $stockService
    ) {}

    public function store(StockOperationRequest $request, Product $product)
    {
        try {
            $type = StockTransactionType::from($request->input('type'));
            match ($type) {
                StockTransactionType::IN     => $this->stockService->stockIn($product, $request->integer('quantity'), $request->input('reason')),
                StockTransactionType::OUT    => $this->stockService->stockOut($product, $request->integer('quantity'), $request->input('reason')),
                StockTransactionType::ADJUST => $this->stockService->adjust($product, $request->integer('quantity'), $request->input('reason')),
            };
            return redirect()->route('products.show', $product)->with('success', "{$type->label()}処理が完了しました");
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error('Stock operation failed', ['product_id' => $product->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => '在庫操作に失敗しました'])->withInput();
        }
    }

    public function index(Request $request)
    {
        $query = StockTransaction::query()->with('product');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('keyword')) {
            $kw = $request->input('keyword');
            $query->whereHas('product', function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('sku', 'like', "%{$kw}%");
            });
        }

        $transactions = $query->latest()->paginate(50);

        return view('stock-transactions.index', compact('transactions'));
    }

    public function export(Request $request)
    {
        $query = StockTransaction::query()->with('product');
        if ($request->filled('type')) $query->where('type', $request->input('type'));
        if ($request->filled('product_id')) $query->where('product_id', $request->integer('product_id'));
        $transactions = $query->latest()->get();
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="stock_transactions_' . now()->format('Ymd_His') . '.csv"'];
        $callback = function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['日時', '商品SKU', '商品名', '操作種別', '数量', '理由']);
            foreach ($transactions as $tx) {
                fputcsv($out, [$tx->created_at->format('Y-m-d H:i:s'), $tx->product->sku ?? '', $tx->product->name ?? '', $tx->type->label(), $tx->quantity, $tx->reason ?? '']);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function bulk()
    {
        $products = Product::orderBy('name')->get();
        return view('stock-transactions.bulk', compact('products'));
    }

    public function bulkStore(Request $request)
    {
        $count = 0;
        foreach ($request->input('entries', []) as $entry) {
            if (empty($entry['product_id']) || empty($entry['quantity']) || (int)$entry['quantity'] <= 0) continue;
            $product = Product::find((int)$entry['product_id']);
            if (!$product) continue;
            $this->stockService->stockIn($product, (int)$entry['quantity'], $entry['reason'] ?? '一括入庫');
            $count++;
        }
        return redirect()->route('stock-transactions.index')->with('success', "{$count}件の商品を入庫しました");
    }

    public function quickAdjust(Request $request, Product $product)
    {
        $request->validate(['delta' => ['required', 'integer', 'in:-1,1']]);
        $delta = $request->integer('delta');
        $delta > 0
            ? $this->stockService->stockIn($product, 1, 'クイック入庫')
            : $this->stockService->stockOut($product, 1, 'クイック出庫');
        return response()->json(['stock' => $product->fresh()->stock_quantity]);
    }
}
