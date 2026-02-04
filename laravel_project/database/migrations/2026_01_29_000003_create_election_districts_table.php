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
        Schema::create('election_districts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 選挙区名（例：東京1区、北海道ブロック）
            $table->string('prefecture')->nullable(); // 都道府県
            $table->enum('type', ['single_seat', 'proportional']); // 小選挙区/比例代表
            $table->enum('house_type', ['house_of_representatives', 'house_of_councillors']); // 衆議院/参議院
            $table->integer('seats')->default(1); // 議席数
            $table->bigInteger('registered_voters')->nullable(); // 有権者数
            $table->json('municipalities')->nullable(); // 含まれる市区町村
            $table->boolean('is_active')->default(true); // 現在有効か
            $table->timestamps();

            $table->index(['house_type', 'type']);
            $table->index('prefecture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_districts');
    }
};
