<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 顧客テーブル拡張（会員機能）
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('member_rank_id')->nullable()->after('address');
            $table->integer('total_points')->default(0)->after('member_rank_id');
            $table->integer('lifetime_spending')->default(0)->after('total_points'); // 累計利用金額
            $table->date('rank_updated_at')->nullable()->after('lifetime_spending');

            $table->foreign('member_rank_id')->references('id')->on('member_ranks')->onDelete('set null');
        });

        // ポイント履歴
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->enum('type', ['earn', 'use', 'expire', 'adjust', 'bonus']);
            $table->integer('points'); // 獲得時は正、使用時は負
            $table->integer('balance_after'); // 処理後残高
            $table->string('description', 200);
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->date('expire_date')->nullable(); // ポイント有効期限
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->index(['customer_id', 'created_at']);
            $table->index('expire_date');
        });

        // お気に入り
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('accommodation_id');
            $table->text('memo')->nullable(); // ユーザーメモ
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->unique(['customer_id', 'accommodation_id']);
        });

        // 検索履歴
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->json('search_params'); // 検索条件
            $table->integer('result_count')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'created_at']);
            $table->index('session_id');
        });

        // 閲覧履歴
        Schema::create('view_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->unsignedBigInteger('accommodation_id');
            $table->integer('view_count')->default(1);
            $table->timestamp('last_viewed_at');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->unique(['customer_id', 'accommodation_id']);
            $table->index('session_id');
        });

        // 予約テーブル拡張
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('room_plan_id')->nullable()->after('room_id');
            $table->json('selected_options')->nullable()->after('room_plan_id'); // 選択オプション
            $table->time('arrival_time')->nullable()->after('selected_options');
            $table->text('special_requests')->nullable()->after('arrival_time');
            $table->enum('booking_source', ['web', 'app', 'phone', 'agency'])->default('web')->after('special_requests');
            $table->integer('points_used')->default(0)->after('booking_source');
            $table->integer('points_earned')->default(0)->after('points_used');
            $table->string('coupon_code', 50)->nullable()->after('points_earned');
            $table->integer('discount_amount')->default(0)->after('coupon_code');

            $table->foreign('room_plan_id')->references('id')->on('room_plans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['room_plan_id']);
            $table->dropColumn([
                'room_plan_id', 'selected_options', 'arrival_time',
                'special_requests', 'booking_source', 'points_used',
                'points_earned', 'coupon_code', 'discount_amount'
            ]);
        });

        Schema::dropIfExists('view_histories');
        Schema::dropIfExists('search_histories');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('point_transactions');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['member_rank_id']);
            $table->dropColumn(['member_rank_id', 'total_points', 'lifetime_spending', 'rank_updated_at']);
        });
    }
};
