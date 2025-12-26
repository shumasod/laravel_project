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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('accommodation_id')->constrained()->onDelete('cascade');
            $table->integer('overall_rating')->unsigned(); // 1-5
            $table->integer('cleanliness_rating')->unsigned()->nullable(); // 1-5
            $table->integer('service_rating')->unsigned()->nullable(); // 1-5
            $table->integer('location_rating')->unsigned()->nullable(); // 1-5
            $table->integer('value_rating')->unsigned()->nullable(); // 1-5
            $table->integer('amenities_rating')->unsigned()->nullable(); // 1-5
            $table->text('title')->nullable();
            $table->text('comment')->nullable();
            $table->json('photos')->nullable(); // 写真のURL配列
            $table->boolean('is_verified')->default(false); // 確認済みのレビュー
            $table->boolean('is_published')->default(true); // 公開/非公開
            $table->text('admin_response')->nullable(); // 管理者からの返信
            $table->timestamp('admin_responded_at')->nullable();
            $table->integer('helpful_count')->default(0); // 役に立ったカウント
            $table->timestamps();

            $table->index('accommodation_id');
            $table->index('overall_rating');
            $table->index('is_published');
            $table->index('created_at');
        });

        // レビューの役立ち評価テーブル
        Schema::create('review_helpful_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['review_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_helpful_votes');
        Schema::dropIfExists('reviews');
    }
};
