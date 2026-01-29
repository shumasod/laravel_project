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
        Schema::create('seat_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('party_id')->constrained('political_parties')->onDelete('cascade');
            $table->integer('predicted_seats'); // 予測議席数
            $table->integer('min_seats')->nullable(); // 最小予測
            $table->integer('max_seats')->nullable(); // 最大予測
            $table->integer('single_seat_prediction')->nullable(); // 小選挙区予測
            $table->integer('proportional_prediction')->nullable(); // 比例代表予測
            $table->decimal('confidence_level', 5, 2)->nullable(); // 信頼度（%）
            $table->json('analysis_factors')->nullable(); // 分析要因
            $table->text('methodology')->nullable(); // 分析手法
            $table->timestamp('predicted_at'); // 予測日時
            $table->timestamps();

            $table->index(['election_id', 'party_id']);
            $table->index('predicted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_predictions');
    }
};
