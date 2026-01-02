<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // 商品識別子（在庫管理では重要な業務キー）
            $table->string('sku', 100)->unique()->comment('Stock Keeping Unit - 商品コード');

            // 基本情報
            $table->string('name', 255)->comment('商品名');
            $table->text('description')->nullable()->comment('商品説明');

            // 在庫数（履歴から復元可能だが、パフォーマンスのため非正規化）
            // WHY: 毎回履歴を集計するとクエリが重くなるため、現在値をキャッシュ
            $table->integer('stock_quantity')->default(0)->comment('現在の在庫数');

            // 発注点（在庫切れアラート用の閾値）
            $table->integer('reorder_point')->default(0)->comment('再発注点（この数値を下回ると警告）');

            // 将来のマルチ倉庫対応を考慮（現時点ではnullable）
            // WHY: 最初から warehouse_id を持たせることで、後からテーブル再設計を避ける
            $table->foreignId('warehouse_id')->nullable()->comment('倉庫ID（将来対応）');

            $table->timestamps();

            // インデックス設計
            $table->index('stock_quantity'); // 在庫一覧のソート用
            $table->index(['reorder_point', 'stock_quantity']); // アラート検索用
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
