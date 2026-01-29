<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // 施設タイプ
            $table->enum('facility_type', [
                'hotel', 'ryokan', 'resort', 'minshuku',
                'guesthouse', 'vacation_rental', 'capsule', 'other'
            ])->default('hotel')->after('description');

            // 詳細説明
            $table->text('description_long')->nullable()->after('facility_type');

            // チェックイン/アウト時間
            $table->time('check_in_time')->default('15:00')->after('description_long');
            $table->time('check_out_time')->default('10:00')->after('check_in_time');
            $table->time('check_in_end')->nullable()->after('check_out_time'); // 最終チェックイン

            // 位置情報
            $table->decimal('latitude', 10, 7)->nullable()->after('check_in_end');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // エリア関連
            $table->unsignedBigInteger('area_id')->nullable()->after('longitude');
            $table->unsignedBigInteger('nearest_station_id')->nullable()->after('area_id');
            $table->integer('station_distance_minutes')->nullable()->after('nearest_station_id'); // 駅からの徒歩分

            // 評価
            $table->tinyInteger('star_rating')->nullable()->after('station_distance_minutes'); // 1-5星
            $table->decimal('review_score', 2, 1)->nullable()->after('star_rating'); // 4.5等
            $table->integer('review_count')->default(0)->after('review_score');

            // 詳細評価
            $table->decimal('cleanliness_score', 2, 1)->nullable();
            $table->decimal('service_score', 2, 1)->nullable();
            $table->decimal('location_score', 2, 1)->nullable();
            $table->decimal('facility_score', 2, 1)->nullable();
            $table->decimal('value_score', 2, 1)->nullable();

            // ハイライト・特徴
            $table->json('highlight_features')->nullable(); // ["温泉", "露天風呂", "朝食バイキング"]

            // 駐車場情報
            $table->json('parking_info')->nullable(); // {available: true, free: true, capacity: 50, reservation_required: false}

            // アクセス情報
            $table->json('access_info')->nullable(); // [{type: "train", description: "JR熱海駅から徒歩5分"}, ...]

            // 最低価格（検索用）
            $table->integer('min_price')->nullable();
            $table->integer('max_price')->nullable();

            // 表示設定
            $table->boolean('is_featured')->default(false); // おすすめ表示
            $table->boolean('is_new')->default(false); // 新着
            $table->integer('display_priority')->default(0);

            // キャンセルポリシー
            $table->unsignedBigInteger('cancellation_policy_id')->nullable();

            // インデックス
            $table->index('facility_type');
            $table->index('area_id');
            $table->index('star_rating');
            $table->index('review_score');
            $table->index('min_price');
            $table->index('is_featured');
            $table->index(['latitude', 'longitude']);

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
            $table->foreign('nearest_station_id')->references('id')->on('stations')->onDelete('set null');
            $table->foreign('cancellation_policy_id')->references('id')->on('cancellation_policies')->onDelete('set null');
        });

        // 施設-アメニティ関連テーブル
        Schema::create('accommodation_amenities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id');
            $table->unsignedBigInteger('amenity_id');
            $table->text('note')->nullable(); // 補足情報
            $table->timestamps();

            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->foreign('amenity_id')->references('id')->on('amenities')->onDelete('cascade');
            $table->unique(['accommodation_id', 'amenity_id']);
        });

        // 施設写真テーブル
        Schema::create('accommodation_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accommodation_id');
            $table->string('url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('caption', 200)->nullable();
            $table->string('category', 50)->nullable(); // exterior/room/bath/meal/facility
            $table->boolean('is_main')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->index(['accommodation_id', 'is_main']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_photos');
        Schema::dropIfExists('accommodation_amenities');

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropForeign(['nearest_station_id']);
            $table->dropForeign(['cancellation_policy_id']);

            $table->dropColumn([
                'facility_type', 'description_long', 'check_in_time', 'check_out_time',
                'check_in_end', 'latitude', 'longitude', 'area_id', 'nearest_station_id',
                'station_distance_minutes', 'star_rating', 'review_score', 'review_count',
                'cleanliness_score', 'service_score', 'location_score', 'facility_score',
                'value_score', 'highlight_features', 'parking_info', 'access_info',
                'min_price', 'max_price', 'is_featured', 'is_new', 'display_priority',
                'cancellation_policy_id'
            ]);
        });
    }
};
