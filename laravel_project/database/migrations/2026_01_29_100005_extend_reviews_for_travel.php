<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // 追加評価項目
            $table->decimal('food_rating', 2, 1)->nullable()->after('amenities_rating');
            $table->decimal('bath_rating', 2, 1)->nullable()->after('food_rating'); // 温泉・風呂

            // 良かった点・改善点
            $table->text('pros')->nullable()->after('comment'); // 良かった点
            $table->text('cons')->nullable()->after('pros'); // 改善点

            // 旅行タイプ
            $table->enum('travel_type', [
                'business', 'solo', 'couple', 'family', 'friends', 'group'
            ])->nullable()->after('cons');

            // 宿泊時期（既存のcreated_atとは別に）
            $table->string('stay_month', 7)->nullable()->after('travel_type'); // 2025-01

            // 写真
            $table->json('photo_urls')->nullable()->after('stay_month');

            // インデックス
            $table->index('travel_type');
            $table->index('stay_month');
        });

        // レビュー写真テーブル（詳細管理用）
        Schema::create('review_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->string('url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('caption', 200)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('review_id')->references('id')->on('reviews')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_photos');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'food_rating', 'bath_rating', 'pros', 'cons',
                'travel_type', 'stay_month', 'photo_urls'
            ]);
        });
    }
};
