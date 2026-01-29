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
        Schema::create('poll_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained('political_parties')->onDelete('cascade');
            $table->foreignId('election_id')->nullable()->constrained()->onDelete('set null'); // 関連選挙
            $table->string('source'); // 調査元（NHK、読売、朝日など）
            $table->enum('poll_type', ['phone', 'online', 'exit_poll', 'mixed']); // 調査方法
            $table->date('survey_start_date'); // 調査開始日
            $table->date('survey_end_date'); // 調査終了日
            $table->decimal('support_rate', 5, 2); // 支持率（%）
            $table->decimal('margin_of_error', 4, 2)->nullable(); // 誤差範囲（%）
            $table->integer('sample_size')->nullable(); // サンプルサイズ
            $table->decimal('response_rate', 5, 2)->nullable(); // 回答率（%）
            $table->json('demographic_breakdown')->nullable(); // 年代別内訳
            $table->json('regional_breakdown')->nullable(); // 地域別内訳
            $table->text('notes')->nullable(); // 備考
            $table->timestamps();

            $table->index(['party_id', 'survey_end_date']);
            $table->index('source');
            $table->index('survey_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poll_data');
    }
};
