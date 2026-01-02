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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();

            // 対象商品（外部キー制約で整合性担保）
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('商品ID');

            // 取引種別（IN: 入庫, OUT: 出庫, ADJUST: 棚卸調整）
            // WHY: ENUMではなくVARCHARにすることで、将来的な拡張（TRANSFER等）に対応
            $table->string('type', 20)->comment('取引種別 (IN/OUT/ADJUST)');

            // 数量（符号付き整数で入出庫を表現）
            // WHY: IN=正, OUT=負 とすることで集計が簡単（SUM()だけで現在値が出る）
            $table->integer('quantity')->comment('増減数量（正=入庫、負=出庫）');

            // 理由・メモ（監査・トラブルシュート用に必須）
            $table->text('reason')->comment('取引理由・メモ');

            // 操作者記録（将来のユーザー管理対応、現時点ではnullable）
            // WHY: 誰が操作したか記録することで、問題発生時の追跡が可能
            $table->foreignId('created_by')
                ->nullable()
                ->comment('操作者ID（将来対応）');

            // 作成日時のみ（履歴は更新しない）
            $table->timestamp('created_at')->useCurrent()->comment('取引日時');

            // インデックス設計
            $table->index('product_id'); // 商品別の履歴検索用
            $table->index(['product_id', 'created_at']); // 商品別の時系列検索用
            $table->index('type'); // 種別別の集計用
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
