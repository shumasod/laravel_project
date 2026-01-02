# 在庫管理システム

**Vibeコーディングアプローチで構築した、実務向け在庫管理システム**

## 🎯 システム概要

小〜中規模事業者向けの在庫管理システム。商品の入庫・出庫・棚卸調整を記録し、在庫数を正確に管理します。

### 主な特徴

- ✅ **完全な監査証跡**: すべての在庫変動を記録
- ✅ **トランザクション安全性**: DBトランザクションで整合性担保
- ✅ **在庫不足アラート**: 発注点を下回ると警告
- ✅ **拡張性**: マルチ倉庫、API連携に対応可能な設計
- ✅ **保守性**: ビジネスロジックをService層に分離

## 📊 アーキテクチャ

### ドメインモデル

```
Product (商品マスター)
├─ id
├─ sku (商品コード)
├─ name
├─ stock_quantity (現在の在庫数)
├─ reorder_point (発注点)
└─ warehouse_id (将来のマルチ倉庫対応)

StockTransaction (在庫履歴)
├─ id
├─ product_id
├─ type (IN / OUT / ADJUST)
├─ quantity (増減数)
├─ reason (理由)
└─ created_by (操作者)
```

### 設計原則

**WHY: 在庫数を2箇所に持つのか？**
- `products.stock_quantity`: パフォーマンス用のキャッシュ
- `stock_transactions`: Single Source of Truth（集計で復元可能）

**WHY: ENUMではなくVARCHARでtypeを保存？**
- 将来的な拡張（TRANSFER, RETURN等）に柔軟に対応

**WHY: 履歴にupdated_atがない？**
- 監査証跡は変更不可。作成日時のみ記録

## 🚀 セットアップ

### 1. マイグレーション実行

```bash
php artisan migrate
```

### 2. 動作確認

```bash
php artisan tinker
```

```php
// 商品登録
$product = Product::create([
    'sku' => 'PROD-001',
    'name' => 'サンプル商品',
    'description' => 'テスト用商品',
    'stock_quantity' => 100,
    'reorder_point' => 20,
]);

// 在庫操作サービス
$service = app(App\Services\StockService::class);

// 入庫
$service->stockIn($product, 50, '仕入れ');

// 出庫
$service->stockOut($product, 30, '販売');

// 棚卸調整
$service->adjust($product, 115, '実地棚卸');

// 在庫確認
$product->fresh()->stock_quantity; // => 115

// 履歴確認
$product->stockTransactions()->get();
```

## 📝 使用方法

### API形式での操作

#### 商品登録

```bash
POST /products
Content-Type: application/json

{
  "sku": "PROD-001",
  "name": "商品名",
  "description": "説明",
  "stock_quantity": 100,
  "reorder_point": 20
}
```

#### 入庫処理

```bash
POST /products/{product}/stock-transactions
Content-Type: application/json

{
  "type": "IN",
  "quantity": 50,
  "reason": "仕入れ"
}
```

#### 出庫処理

```bash
POST /products/{product}/stock-transactions
Content-Type: application/json

{
  "type": "OUT",
  "quantity": 30,
  "reason": "販売"
}
```

#### 棚卸調整

```bash
POST /products/{product}/stock-transactions
Content-Type: application/json

{
  "type": "ADJUST",
  "quantity": 115,
  "reason": "実地棚卸"
}
```

### Service層の直接利用（推奨）

```php
use App\Services\StockService;
use App\Models\Product;

$stockService = app(StockService::class);
$product = Product::where('sku', 'PROD-001')->first();

// 入庫
$stockService->stockIn($product, 50, '仕入れ');

// 出庫（在庫不足時は例外発生）
try {
    $stockService->stockOut($product, 1000, '販売');
} catch (\InvalidArgumentException $e) {
    // "在庫不足: 現在庫150個に対して1000個の出庫はできません"
}

// 棚卸調整（実地棚卸の結果を反映）
$stockService->adjust($product, 145, '月次棚卸');
```

## 🔍 在庫整合性チェック

```php
$stockService = app(App\Services\StockService::class);
$product = Product::find(1);

$result = $stockService->verifyStockIntegrity($product);

/*
[
    'is_valid' => true,
    'expected' => 150,  // 履歴から計算した値
    'actual' => 150,    // products.stock_quantity
]
*/
```

**運用推奨**: 定期的にバッチ実行して不整合を検出

```php
// Laravel Scheduler に登録
Schedule::call(function () {
    Product::chunk(100, function ($products) {
        $service = app(StockService::class);
        foreach ($products as $product) {
            $result = $service->verifyStockIntegrity($product);
            if (!$result['is_valid']) {
                Log::error('Stock mismatch', [
                    'product_id' => $product->id,
                    'expected' => $result['expected'],
                    'actual' => $result['actual'],
                ]);
            }
        }
    });
})->daily();
```

## 📦 拡張方法

### 1. マルチ倉庫対応

```php
// Migration
Schema::create('warehouses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code');
});

// products.warehouse_id の外部キー制約を追加
Schema::table('products', function (Blueprint $table) {
    $table->foreign('warehouse_id')->references('id')->on('warehouses');
});

// Service拡張
public function transfer(Product $product, int $fromWarehouse, int $toWarehouse, int $quantity, string $reason)
{
    // 倉庫間移動ロジック
}
```

### 2. API化

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/stock-in', [StockTransactionController::class, 'stockIn']);
    Route::post('products/{product}/stock-out', [StockTransactionController::class, 'stockOut']);
});

// API Resource作成
php artisan make:resource ProductResource
php artisan make:resource StockTransactionResource
```

### 3. 在庫不足通知（Slack連携）

```php
// StockService.php
use Illuminate\Support\Facades\Notification;

public function stockOut(Product $product, int $quantity, string $reason, ?int $createdBy = null): StockTransaction
{
    $transaction = DB::transaction(function () use ($product, $quantity, $reason, $createdBy) {
        // ... 既存の処理 ...

        // 発注点を下回ったら通知
        if ($product->fresh()->isBelowReorderPoint()) {
            Notification::route('slack', config('services.slack.webhook'))
                ->notify(new LowStockAlert($product));
        }

        return $transaction;
    });
}
```

### 4. QRコード対応

```bash
composer require simplesoftwareio/simple-qrcode
```

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// 商品にQRコード生成
public function generateQrCode(Product $product)
{
    return QrCode::size(200)->generate($product->sku);
}

// スキャン→入出庫
Route::post('/scan-stock-in', function (Request $request, StockService $service) {
    $sku = $request->input('qr_code'); // QRから読み取ったSKU
    $product = Product::where('sku', $sku)->firstOrFail();

    $service->stockIn($product, $request->integer('quantity'), 'QRスキャン入庫');
});
```

## 🧪 テスト

### Feature Test

```php
// tests/Feature/StockServiceTest.php
public function test_stock_in_increases_quantity()
{
    $product = Product::factory()->create(['stock_quantity' => 100]);
    $service = app(StockService::class);

    $service->stockIn($product, 50, 'テスト入庫');

    $this->assertEquals(150, $product->fresh()->stock_quantity);
    $this->assertDatabaseHas('stock_transactions', [
        'product_id' => $product->id,
        'type' => 'IN',
        'quantity' => 50,
    ]);
}

public function test_stock_out_prevents_negative_inventory()
{
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $service = app(StockService::class);

    $this->expectException(\InvalidArgumentException::class);
    $service->stockOut($product, 50, 'テスト出庫');
}
```

## 🛡 セキュリティ考慮事項

- [ ] 認証・認可の実装（Laravel Breezeなど）
- [ ] API Rate Limiting
- [ ] CSRFトークン検証
- [ ] SQL Injection対策（Eloquent使用で対応済み）
- [ ] XSS対策（Blade使用で対応済み）

## 📈 パフォーマンス最適化

### インデックス設計（実装済み）

```php
// products
$table->index('stock_quantity'); // 在庫ソート用
$table->index(['reorder_point', 'stock_quantity']); // アラート検索用

// stock_transactions
$table->index('product_id'); // 商品別履歴
$table->index(['product_id', 'created_at']); // 時系列検索
```

### N+1問題対策

```php
// 商品一覧で履歴も取得する場合
$products = Product::with('stockTransactions')->paginate(20);

// 在庫履歴一覧で商品情報も取得
$transactions = StockTransaction::with('product')->latest()->paginate(50);
```

## 🚨 トラブルシューティング

### 在庫数と履歴が合わない場合

```php
$product = Product::find(1);
$service = app(StockService::class);

// 整合性チェック
$result = $service->verifyStockIntegrity($product);

if (!$result['is_valid']) {
    // 履歴から再計算して修正
    $correctStock = $product->stockTransactions()->sum('quantity');
    $product->update(['stock_quantity' => $correctStock]);

    // 調整履歴を記録
    $service->adjust($product, $correctStock, '整合性修正');
}
```

### マイグレーションエラー

```bash
# ロールバック
php artisan migrate:rollback

# 再実行
php artisan migrate

# フレッシュスタート（データ削除注意）
php artisan migrate:fresh
```

## 📚 参考資料

- [Laravel Documentation](https://laravel.com/docs)
- [Database Transactions](https://laravel.com/docs/database#database-transactions)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Service Layer Pattern](https://martinfowler.com/eaaCatalog/serviceLayer.html)

## 🔄 今後の拡張案

- [ ] フロントエンド実装（Inertia.js + React）
- [ ] リアルタイム在庫更新（WebSocket）
- [ ] CSVインポート/エクスポート
- [ ] バーコード/QRコード対応
- [ ] マルチ倉庫管理
- [ ] 発注管理機能
- [ ] 売上データ連携
- [ ] 在庫予測（AI）

---

**作成日**: 2026-01-02
**バージョン**: 1.0.0
**ライセンス**: MIT
