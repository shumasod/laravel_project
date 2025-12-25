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
        Schema::table('customers', function (Blueprint $table) {
            // 個人情報保護のためのフィールド
            $table->dateTime('last_stay_date')->nullable()->after('address');
            $table->integer('total_stays')->default(0)->after('last_stay_date');
            $table->decimal('total_spent', 12, 2)->default(0)->after('total_stays');

            // データ保護同意
            $table->boolean('privacy_consent')->default(false)->after('total_spent');
            $table->dateTime('privacy_consent_date')->nullable()->after('privacy_consent');

            // マーケティング同意
            $table->boolean('marketing_consent')->default(false)->after('privacy_consent_date');

            // データ削除リクエスト（GDPR対応）
            $table->boolean('deletion_requested')->default(false)->after('marketing_consent');
            $table->dateTime('deletion_requested_at')->nullable()->after('deletion_requested');

            // ソフトデリート
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'last_stay_date',
                'total_stays',
                'total_spent',
                'privacy_consent',
                'privacy_consent_date',
                'marketing_consent',
                'deletion_requested',
                'deletion_requested_at'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
