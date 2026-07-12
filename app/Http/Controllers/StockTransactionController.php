<?php

namespace App\Http\Controllers;

use App\Enums\StockTransactionType;
use App\Http\Requests\StockOperationRequest;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 在庫操作コントローラー
 *
 * WHY: 在庫の入出庫操作はServiceに完全委譲し、Controllerは薄く保つ
 */
class StockTransactionController extends Controller
{
    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * 在庫操作実行（入庫/出庫/調整）
     */
    public function store(StockOperationRequest $request, Product $product)
    {
        try {
            $type = StockTransactionType::from($request->input('type'));
            $quantity = $request->integer('quantity');
            $reason = $request->input('reason');

            // WHY: 操作種別に応じて適切なServiceメソッドを呼び出す
            $transaction = match ($type) {
                StockTransactionType::IN => $this->stockService->stockIn(
                    $product,
                    $quantity,
                    $reason
                ),
                StockTransactionType::OUT => $this->stockService->stockOut(
                    $product,
                    $quantity,
                    $reason
                ),
                StockTransactionType::ADJUST => $this->stockService->adjust(
                    $product,
                    $quantity,
                    $reason
                ),
            };

            return redirect()
                ->route('products.show', $product)
                ->with('success', "{$type->label()}処理が完了しました");

        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['quantity' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Stock operation failed', [
                'product_id' => $product->id,
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => '在庫操作に失敗しました'])
                ->withInput();
        }
    }

    /**
     * 在庫履歴一覧
     */
    public function index(Request $request)
    {
        $query = \App\Models\StockTransaction::query()
            ->with('product');

        // フィルタ
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        $transactions = $query->latest()->paginate(50);

        return view('stock-transactions.index', compact('transactions'));
    }

    /**
     * Quick +1/-1 stock adjust via JSON for the products index page buttons.
     */
    public function quickAdjust(Request $request, Product $product)
    {
        $delta = $request->integer('delta');
        if (!in_array($delta, [-1, 1], true)) {
            return response()->json(['error' => 'invalid delta'], 422);
        }

        try {
            if ($delta > 0) {
                $this->stockService->stockIn($product, 1, 'クイック入庫');
            } else {
                $this->stockService->stockOut($product, 1, 'クイック出庫');
            }
            $product->refresh();
            return response()->json(['stock' => $product->stock_quantity]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
