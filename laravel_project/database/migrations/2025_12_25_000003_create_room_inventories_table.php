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
        Schema::create('room_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained()->onDelete('cascade');
            $table->string('room_type');
            $table->date('date');
            $table->integer('total_rooms')->default(0);
            $table->integer('available_rooms')->default(0);
            $table->integer('reserved_rooms')->default(0);
            $table->timestamps();

            // ユニーク制約：同じ宿泊施設・部屋タイプ・日付の組み合わせは1つのみ
            $table->unique(['accommodation_id', 'room_type', 'date'], 'inventory_unique');

            // インデックス
            $table->index(['accommodation_id', 'date']);
            $table->index('room_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_inventories');
    }
};
