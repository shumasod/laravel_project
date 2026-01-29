<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // エリアマスタ（都道府県・市区町村）
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_kana', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('level', ['region', 'prefecture', 'city', 'district']); // 地方/都道府県/市区町村/地区
            $table->string('code', 10)->nullable(); // 行政コード
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('accommodation_count')->default(0);
            $table->integer('display_order')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('areas')->onDelete('set null');
            $table->index(['level', 'parent_id']);
            $table->index('is_popular');
        });

        // 駅マスタ
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_kana', 100)->nullable();
            $table->string('line_name', 100)->nullable(); // 路線名
            $table->string('line_code', 20)->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_major')->default(false); // 主要駅フラグ
            $table->timestamps();

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
            $table->index('name');
            $table->index('is_major');
        });

        // 観光スポットマスタ
        Schema::create('landmarks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('name_kana', 200)->nullable();
            $table->string('category', 50); // 温泉地/テーマパーク/自然/歴史/etc
            $table->unsignedBigInteger('area_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_popular')->default(false);
            $table->timestamps();

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
            $table->index(['category', 'is_popular']);
        });

        // アメニティカテゴリ
        Schema::create('amenity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_en', 100)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // アメニティマスタ
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 100);
            $table->string('name_en', 100)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_highlight')->default(false); // ハイライト表示
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('amenity_categories')->onDelete('cascade');
        });

        // キャンセルポリシーマスタ
        Schema::create('cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->text('description');
            $table->json('rules'); // [{days_before: 7, charge_percent: 0}, {days_before: 3, charge_percent: 50}, ...]
            $table->timestamps();
        });

        // 会員ランクマスタ
        Schema::create('member_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 50);
            $table->integer('min_spending')->default(0); // 最低利用金額
            $table->decimal('point_rate', 3, 2)->default(0.01); // ポイント還元率
            $table->string('color', 20)->nullable();
            $table->json('benefits')->nullable(); // 特典一覧
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_ranks');
        Schema::dropIfExists('cancellation_policies');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('amenity_categories');
        Schema::dropIfExists('landmarks');
        Schema::dropIfExists('stations');
        Schema::dropIfExists('areas');
    }
};
