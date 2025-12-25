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
        Schema::table('reservations', function (Blueprint $table) {
            // ステータスを拡張（仮予約、確定、チェックイン、チェックアウト、キャンセル、ノーショー）
            $table->dropColumn('status');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('status', [
                'provisional',      // 仮予約
                'confirmed',        // 確定
                'checked_in',       // チェックイン
                'checked_out',      // チェックアウト
                'cancelled',        // キャンセル
                'no_show'          // ノーショー
            ])->default('provisional')->after('check_out_date');

            // 実際のチェックイン・チェックアウト時刻
            $table->dateTime('actual_check_in_time')->nullable()->after('status');
            $table->dateTime('actual_check_out_time')->nullable()->after('actual_check_in_time');

            // 宿泊人数
            $table->integer('number_of_guests')->default(1)->after('room_id');

            // 早割・直前割などの適用情報
            $table->json('applied_discounts')->nullable()->after('total_amount');

            // 料金の詳細内訳
            $table->json('price_breakdown')->nullable()->after('applied_discounts');

            // キャンセル時の情報
            $table->dateTime('cancelled_at')->nullable()->after('price_breakdown');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');

            // 予約者のユーザーID（権限管理用）
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->after('customer_id');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->after('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'actual_check_in_time',
                'actual_check_out_time',
                'number_of_guests',
                'applied_discounts',
                'price_breakdown',
                'cancelled_at',
                'cancellation_reason',
                'created_by_user_id',
                'updated_by_user_id'
            ]);
            $table->dropColumn('status');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
        });
    }
};
