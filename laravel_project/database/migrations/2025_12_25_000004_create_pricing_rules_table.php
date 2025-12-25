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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained()->onDelete('cascade');
            $table->string('room_type')->nullable();
            $table->string('rule_type'); // day_of_week, season, extra_guest, consecutive_nights, early_bird, last_minute
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions'); // ルールの適用条件
            $table->enum('calculation_type', ['fixed', 'percentage', 'multiplier']); // 計算方法
            $table->decimal('value', 10, 2); // 金額または割合
            $table->integer('priority')->default(0); // 優先度（複数ルール適用時）
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();

            // インデックス
            $table->index(['accommodation_id', 'rule_type']);
            $table->index('is_active');
            $table->index(['valid_from', 'valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
