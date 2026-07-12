<?php

namespace App\Http\Controllers;

use App\Enums\StockTransactionType;
use App\Http\Requests\StockOperationRequest;
use App\Models\Product;
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
            $quantity = $request->integer('quantity');
            $reason = $request->input('reason');

            $transaction = match ($type) {
                StockTransactionType::IN => $this->stockService->stockIn($product, $quantity, $reason),
                StockTransactionType::OUT => $this->stockService->stockOut($product, $quantity, $reason),
                StockTransactionType::ADJUST => $this->stockService->adjust($product, $quantity, $reason),
            };

            return redirect()
                ->route('products.show', $product)
                ->with('success', "{$type->label()}処理が完了しました");

        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error('Stock operation failed', [
                'product_id' => $product->id,
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => '在庫操作に失敗しました'])->withInput();
        }
    }

    public function index(Request $request)
    {
        $query = \App\Models\StockTransaction::query()->with('product');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        $transactions = $query->latest()->paginate(50);

        return view('stock-transactions.index', compact('transactions'));
    }

    public function export(Request $request)
    {
        $query = \App\Models\StockTransaction::query()->with('product');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        $transactions = $query->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="stock_transactions_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['日時', '商品SKU', '商品名', '操作種別', '数量', '理由']);
            foreach ($transactions as $tx) {
                fputcsv($out, [
                    $tx->created_at->format('Y-m-d H:i:s'),
                    $tx->product->sku ?? '',
                    $tx->product->name ?? '',
                    $tx->type->label(),
                    $tx->quantity,
                    $tx->reason ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
