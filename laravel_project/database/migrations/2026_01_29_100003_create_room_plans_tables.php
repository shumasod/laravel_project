<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 部屋タイプ拡張（既存roomsテーブルを拡張）
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('room_type_name', 100)->nullable()->after('room_type'); // ツインルーム等
            $table->integer('square_meters')->nullable()->after('room_type_name');
            $table->enum('bed_type', [
                'single', 'semi_double', 'double', 'queen', 'king',
                'twin', 'triple', 'futon', 'mixed'
            ])->nullable()->after('square_meters');
            $table->integer('bed_count')->nullable()->after('bed_type');
            $table->integer('max_occupancy')->nullable()->after('capacity');
            $table->integer('base_price_weekday')->nullable()->after('price_per_night');
            $table->integer('base_price_weekend')->nullable()->after('base_price_weekday');
            $table->json('room_amenities')->nullable(); // 部屋備品
            $table->json('room_features')->nullable(); // 特徴（オーシャンビュー等）
            $table->string('main_image_url', 500)->nullable();
            $table->text('room_description')->nullable();
            $table->boolean('is_smoking')->default(false);
            $table->integer('display_order')->default(0);

            $table->index('room_type');
            $table->index('max_occupancy');
            $table->index('is_smoking');
        });

        // 宿泊プラン
        Schema::create('room_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->string('name', 200);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();

            // 食事条件
            $table->enum('meal_type', [
                'room_only', 'breakfast_only', 'dinner_only',
                'half_board', 'full_board', 'all_inclusive'
            ])->default('room_only');
            $table->text('meal_description')->nullable();

            // 料金設定
            $table->integer('base_price'); // 基本料金（1人1泊）
            $table->integer('child_price')->nullable();
            $table->integer('infant_price')->nullable();
            $table->json('date_prices')->nullable(); // 日付別料金 {2025-02-14: 15000, ...}

            // 販売条件
            $table->date('sale_start_date')->nullable();
            $table->date('sale_end_date')->nullable();
            $table->date('stay_start_date')->nullable(); // 宿泊可能期間
            $table->date('stay_end_date')->nullable();
            $table->integer('min_nights')->default(1);
            $table->integer('max_nights')->nullable();
            $table->integer('min_guests')->default(1);
            $table->integer('max_guests')->nullable();
            $table->json('available_days')->nullable(); // 販売曜日 [0,1,2,3,4,5,6]

            // キャンセルポリシー
            $table->unsignedBigInteger('cancellation_policy_id')->nullable();

            // ポイント・特典
            $table->decimal('point_rate', 3, 2)->nullable(); // ポイント還元率
            $table->json('benefits')->nullable(); // 特典一覧

            // 表示設定
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('badge_text', 50)->nullable(); // 「人気」「期間限定」等
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('cancellation_policy_id')->references('id')->on('cancellation_policies')->onDelete('set null');
            $table->index(['room_id', 'is_active']);
            $table->index(['sale_start_date', 'sale_end_date']);
        });

        // プランオプション（レイトチェックアウト等）
        Schema::create('plan_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('price');
            $table->enum('price_type', ['per_person', 'per_room', 'per_stay']);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        // プラン-オプション関連
        Schema::create('room_plan_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_plan_id');
            $table->unsignedBigInteger('plan_option_id');
            $table->integer('override_price')->nullable();

            $table->foreign('room_plan_id')->references('id')->on('room_plans')->onDelete('cascade');
            $table->foreign('plan_option_id')->references('id')->on('plan_options')->onDelete('cascade');
            $table->unique(['room_plan_id', 'plan_option_id']);
        });

        // プラン在庫
        Schema::create('plan_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_plan_id');
            $table->date('date');
            $table->integer('total_inventory');
            $table->integer('available_inventory');
            $table->integer('price')->nullable(); // その日の特別価格
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->foreign('room_plan_id')->references('id')->on('room_plans')->onDelete('cascade');
            $table->unique(['room_plan_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_inventories');
        Schema::dropIfExists('room_plan_options');
        Schema::dropIfExists('plan_options');
        Schema::dropIfExists('room_plans');

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'room_type_name', 'square_meters', 'bed_type', 'bed_count',
                'max_occupancy', 'base_price_weekday', 'base_price_weekend',
                'room_amenities', 'room_features', 'main_image_url',
                'room_description', 'is_smoking', 'display_order'
            ]);
        });
    }
};
