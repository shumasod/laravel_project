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
        Schema::create('guest_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            // 個人情報保護を考慮した暗号化フィールド
            $table->text('allergies')->nullable(); // アレルギー情報
            $table->text('dietary_restrictions')->nullable(); // 食事制限
            $table->text('special_requests')->nullable(); // 特別なリクエスト

            // 嗜好情報
            $table->boolean('smoking_preference')->default(false); // 喫煙希望
            $table->enum('bed_preference', ['single', 'double', 'twin', 'any'])->default('any');
            $table->enum('floor_preference', ['low', 'high', 'any'])->default('any');
            $table->boolean('quiet_room_preference')->default(false);

            // 連絡先の優先設定
            $table->enum('preferred_contact_method', ['email', 'phone', 'sms'])->default('email');
            $table->string('preferred_language')->default('ja');

            // その他のメモ
            $table->text('notes')->nullable();

            $table->timestamps();

            // ユニーク制約：顧客ごとに1つの設定のみ
            $table->unique('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_preferences');
    }
};
