<?php

namespace App\Http\Controllers;

use App\Enums\StockTransactionType;
use App\Http\Requests\StockOperationRequest;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('admin');

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
        Gate::authorize('admin');

        $request->validate([
            'type'       => 'nullable|in:IN,OUT,ADJUST',
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

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
}
