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
        Schema::create('political_parties', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 政党名
            $table->string('short_name')->nullable(); // 略称
            $table->string('english_name')->nullable(); // 英語名
            $table->string('color', 7)->nullable(); // シンボルカラー（#RRGGBB）
            $table->date('founded_date')->nullable(); // 設立日
            $table->date('dissolved_date')->nullable(); // 解散日
            $table->text('description')->nullable(); // 説明
            $table->boolean('is_active')->default(true); // 現存するか
            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('political_parties');
    }
};
