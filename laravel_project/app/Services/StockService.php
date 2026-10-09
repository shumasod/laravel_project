<?php

namespace App\Services;

use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * 在庫操作サービス
 *
 * WHY: ビジネスロジックをControllerから分離し、再利用可能にする
 * トランザクション管理を一箇所に集約し、整合性を担保する
 */
class StockService
{
    /**
     * 入庫処理
     *
     * @param Product $product 対象商品
     * @param int $quantity 入庫数量（正の数）
     * @param string $reason 入庫理由
     * @param int|null $createdBy 操作者ID
     * @return StockTransaction
     * @throws InvalidArgumentException
     */
    public function stockIn(Product $product, int $quantity, string $reason, ?int $createdBy = null): StockTransaction
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('入庫数量は正の数である必要があります');
        }

        return DB::transaction(function () use ($product, $quantity, $reason, $createdBy) {
            // 1. 在庫取引を記録
            $transaction = StockTransaction::create([
                'product_id' => $product->id,
                'type' => StockTransactionType::IN,
                'quantity' => $quantity,
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);

            // 2. 商品の在庫数を更新
            $product->increment('stock_quantity', $quantity);

            // 3. 監査ログ
            Log::info('Stock IN', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'quantity' => $quantity,
                'new_stock' => $product->fresh()->stock_quantity,
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);

            return $transaction;
        });
    }

    /**
     * 出庫処理
     *
     * @param Product $product 対象商品
     * @param int $quantity 出庫数量（正の数）
     * @param string $reason 出庫理由
     * @param int|null $createdBy 操作者ID
     * @return StockTransaction
     * @throws InvalidArgumentException
     */
    public function stockOut(Product $product, int $quantity, string $reason, ?int $createdBy = null): StockTransaction
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('出庫数量は正の数である必要があります');
        }

        // WHY: 在庫がマイナスになることを防ぐ（ビジネスルール）
        if ($product->stock_quantity < $quantity) {
            throw new InvalidArgumentException('在庫が不足しています。現在の在庫数を確認してください。');
        }

        return DB::transaction(function () use ($product, $quantity, $reason, $createdBy) {
            // 1. 在庫取引を記録（マイナス数量で記録）
            $transaction = StockTransaction::create([
                'product_id' => $product->id,
                'type' => StockTransactionType::OUT,
                'quantity' => -$quantity, // 出庫は負の数で記録
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);

            // 2. 商品の在庫数を減算
            $product->decrement('stock_quantity', $quantity);

            // 3. 監査ログ
            Log::info('Stock OUT', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'quantity' => $quantity,
                'new_stock' => $product->fresh()->stock_quantity,
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);

            return $transaction;
        });
    }

    /**
     * 棚卸調整
     *
     * @param Product $product 対象商品
     * @param int $actualQuantity 実地棚卸の数量
     * @param string $reason 調整理由
     * @param int|null $createdBy 操作者ID
     * @return StockTransaction
     */
    public function adjust(Product $product, int $actualQuantity, string $reason, ?int $createdBy = null): StockTransaction
    {
        if ($actualQuantity < 0) {
            throw new InvalidArgumentException('実地棚卸数量は0以上である必要があります');
        }

        return DB::transaction(function () use ($product, $actualQuantity, $reason, $createdBy) {
            $currentStock = $product->stock_quantity;
            $difference = $actualQuantity - $currentStock;

            // 1. 在庫取引を記録（差分を記録）
            $transaction = StockTransaction::create([
                'product_id' => $product->id,
                'type' => StockTransactionType::ADJUST,
                'quantity' => $difference,
                'reason' => $reason . " (システム在庫: {$currentStock} → 実在庫: {$actualQuantity})",
                'created_by' => $createdBy,
            ]);

            // 2. 商品の在庫数を実地棚卸の値に更新
            $product->stock_quantity = $actualQuantity;
            $product->save();

            // 3. 監査ログ（棚卸差異は重要なので別途記録）
            Log::warning('Stock ADJUST', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'system_stock' => $currentStock,
                'actual_stock' => $actualQuantity,
                'difference' => $difference,
                'reason' => $reason,
                'created_by' => $createdBy,
            ]);

            return $transaction;
        });
    }

    /**
     * 在庫整合性チェック（運用・デバッグ用）
     *
     * WHY: 履歴から計算した値と products.stock_quantity が一致するか検証
     * 定期的にバッチで実行することで、データ不整合を早期発見できる
     *
     * @param Product $product
     * @return array ['is_valid' => bool, 'expected' => int, 'actual' => int]
     */
    public function verifyStockIntegrity(Product $product): array
    {
        // 履歴の合計値を計算
        $expectedStock = $product->stockTransactions()->sum('quantity');
        $actualStock = $product->stock_quantity;

        $isValid = ($expectedStock === $actualStock);

        if (!$isValid) {
            Log::error('Stock integrity mismatch', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'expected' => $expectedStock,
                'actual' => $actualStock,
                'difference' => $actualStock - $expectedStock,
            ]);
        }

        return [
            'is_valid' => $isValid,
            'expected' => $expectedStock,
            'actual' => $actualStock,
        ];
    }
}
