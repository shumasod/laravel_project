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
        Schema::create('election_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->foreignId('district_id')->constrained('election_districts')->onDelete('cascade');
            $table->foreignId('party_id')->constrained('political_parties')->onDelete('cascade');
            $table->string('candidate_name')->nullable(); // 候補者名（小選挙区の場合）
            $table->bigInteger('votes')->default(0); // 得票数
            $table->decimal('vote_share', 5, 2)->nullable(); // 得票率（%）
            $table->integer('seats_won')->default(0); // 獲得議席数
            $table->boolean('is_winner')->default(false); // 当選か
            $table->integer('rank')->nullable(); // 順位
            $table->text('notes')->nullable(); // 備考
            $table->timestamps();

            $table->index(['election_id', 'party_id']);
            $table->index(['election_id', 'district_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_results');
    }
};
