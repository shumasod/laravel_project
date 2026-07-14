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
    public function __construct(private StockService $stockService) {}

    public function store(StockOperationRequest $request, Product $product)
    {
        try {
            $type     = StockTransactionType::from($request->input('type'));
            $quantity = $request->integer('quantity');
            $reason   = $request->input('reason');

            match ($type) {
                StockTransactionType::IN     => $this->stockService->stockIn($product, $quantity, $reason),
                StockTransactionType::OUT    => $this->stockService->stockOut($product, $quantity, $reason),
                StockTransactionType::ADJUST => $this->stockService->adjust($product, $quantity, $reason),
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
        $query = \App\Models\StockTransaction::query()->with('product');
        if ($request->filled('type'))       { $query->where('type', $request->input('type')); }
        if ($request->filled('product_id')) { $query->where('product_id', $request->integer('product_id')); }
        return view('stock-transactions.index', ['transactions' => $query->latest()->paginate(50)]);
    }

    public function bulk()
    {
        $products = Product::orderBy('name')->get(['id', 'sku', 'name', 'stock_quantity']);
        return view('stock-transactions.bulk', compact('products'));
    }

    public function bulkStore(Request $request)
    {
        $entries = $request->input('entries', []);
        $count   = 0;

        foreach ($entries as $entry) {
            if (empty($entry['product_id']) || empty($entry['quantity']) || (int) $entry['quantity'] <= 0) {
                continue;
            }
            $product = Product::find((int) $entry['product_id']);
            if (!$product) { continue; }

            $this->stockService->stockIn(
                $product,
                (int) $entry['quantity'],
                $entry['reason'] ?? '一括入庫'
            );
            $count++;
        }

        return redirect()
            ->route('stock-transactions.index')
            ->with('success', "{$count}件の商品を入庫しました");
    }
}
