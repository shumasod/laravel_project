<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Station;
use App\Models\Landmark;
use App\Models\Amenity;
use App\Models\AmenityCategory;
use App\Models\CancellationPolicy;
use App\Models\MemberRank;
use App\Models\Accommodation;
use App\Models\Room;
use App\Models\RoomPlan;
use App\Models\PlanInventory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TravelSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAreas();
        $this->seedAmenities();
        $this->seedCancellationPolicies();
        $this->seedMemberRanks();
        $this->seedSampleAccommodations();
    }

    private function seedAreas(): void
    {
        // 地方
        $regions = [
            ['name' => '北海道・東北', 'name_en' => 'Hokkaido/Tohoku'],
            ['name' => '関東', 'name_en' => 'Kanto'],
            ['name' => '中部', 'name_en' => 'Chubu'],
            ['name' => '関西', 'name_en' => 'Kansai'],
            ['name' => '中国・四国', 'name_en' => 'Chugoku/Shikoku'],
            ['name' => '九州・沖縄', 'name_en' => 'Kyushu/Okinawa'],
        ];

        foreach ($regions as $i => $region) {
            Area::firstOrCreate(
                ['name' => $region['name'], 'level' => 'region'],
                ['name_en' => $region['name_en'], 'display_order' => $i]
            );
        }

        // 都道府県
        $prefectures = [
            '関東' => [
                ['name' => '東京都', 'code' => '13', 'lat' => 35.6762, 'lng' => 139.6503, 'popular' => true],
                ['name' => '神奈川県', 'code' => '14', 'lat' => 35.4478, 'lng' => 139.6425, 'popular' => true],
                ['name' => '千葉県', 'code' => '12', 'lat' => 35.6050, 'lng' => 140.1233],
                ['name' => '埼玉県', 'code' => '11', 'lat' => 35.8569, 'lng' => 139.6489],
            ],
            '関西' => [
                ['name' => '大阪府', 'code' => '27', 'lat' => 34.6937, 'lng' => 135.5023, 'popular' => true],
                ['name' => '京都府', 'code' => '26', 'lat' => 35.0116, 'lng' => 135.7681, 'popular' => true],
                ['name' => '兵庫県', 'code' => '28', 'lat' => 34.6913, 'lng' => 135.1830],
                ['name' => '奈良県', 'code' => '29', 'lat' => 34.6851, 'lng' => 135.8048],
            ],
            '北海道・東北' => [
                ['name' => '北海道', 'code' => '01', 'lat' => 43.0642, 'lng' => 141.3469, 'popular' => true],
                ['name' => '宮城県', 'code' => '04', 'lat' => 38.2688, 'lng' => 140.8721],
            ],
            '中部' => [
                ['name' => '静岡県', 'code' => '22', 'lat' => 34.9769, 'lng' => 138.3831, 'popular' => true],
                ['name' => '愛知県', 'code' => '23', 'lat' => 35.1802, 'lng' => 136.9066],
                ['name' => '長野県', 'code' => '20', 'lat' => 36.6513, 'lng' => 138.1810],
            ],
            '九州・沖縄' => [
                ['name' => '福岡県', 'code' => '40', 'lat' => 33.6064, 'lng' => 130.4183, 'popular' => true],
                ['name' => '沖縄県', 'code' => '47', 'lat' => 26.2124, 'lng' => 127.6809, 'popular' => true],
            ],
        ];

        foreach ($prefectures as $regionName => $prefs) {
            $region = Area::where('name', $regionName)->first();
            foreach ($prefs as $i => $pref) {
                Area::firstOrCreate(
                    ['name' => $pref['name'], 'level' => 'prefecture'],
                    [
                        'parent_id' => $region?->id,
                        'code' => $pref['code'],
                        'latitude' => $pref['lat'],
                        'longitude' => $pref['lng'],
                        'is_popular' => $pref['popular'] ?? false,
                        'display_order' => $i,
                        'accommodation_count' => rand(100, 2000),
                    ]
                );
            }
        }

        // 人気エリア（市区町村レベル）
        $popularCities = [
            ['name' => '渋谷区', 'parent' => '東京都', 'popular' => true, 'count' => 150],
            ['name' => '新宿区', 'parent' => '東京都', 'popular' => true, 'count' => 200],
            ['name' => '熱海市', 'parent' => '静岡県', 'popular' => true, 'count' => 120],
            ['name' => '箱根町', 'parent' => '神奈川県', 'popular' => true, 'count' => 180],
            ['name' => '京都市', 'parent' => '京都府', 'popular' => true, 'count' => 350],
            ['name' => '札幌市', 'parent' => '北海道', 'popular' => true, 'count' => 280],
            ['name' => '那覇市', 'parent' => '沖縄県', 'popular' => true, 'count' => 220],
            ['name' => '難波', 'parent' => '大阪府', 'popular' => true, 'count' => 180],
        ];

        foreach ($popularCities as $city) {
            $parent = Area::where('name', $city['parent'])->first();
            Area::firstOrCreate(
                ['name' => $city['name'], 'level' => 'city'],
                [
                    'parent_id' => $parent?->id,
                    'is_popular' => $city['popular'],
                    'accommodation_count' => $city['count'],
                ]
            );
        }
    }

    private function seedAmenities(): void
    {
        $categories = [
            '基本設備' => ['無料Wi-Fi', '駐車場（無料）', '駐車場（有料）', 'エレベーター', 'バリアフリー', '禁煙ルーム'],
            '温泉・風呂' => ['温泉', '露天風呂', '貸切風呂', '大浴場', 'サウナ', '源泉掛け流し'],
            '食事・飲食' => ['レストラン', 'バー/ラウンジ', 'ルームサービス', '自動販売機', '共用キッチン'],
            'レジャー' => ['プール', 'フィットネス', 'スパ/エステ', 'カラオケ', 'ゲームコーナー'],
            'サービス' => ['24時間フロント', '荷物預かり', '宅配便', 'コンシェルジュ', '送迎サービス'],
            '部屋設備' => ['エアコン', '冷蔵庫', 'テレビ', '金庫', 'ドライヤー', 'バスタブ', '洗浄機能付きトイレ'],
        ];

        foreach ($categories as $catName => $amenities) {
            $category = AmenityCategory::firstOrCreate(['name' => $catName]);

            foreach ($amenities as $i => $amenityName) {
                Amenity::firstOrCreate(
                    ['name' => $amenityName, 'category_id' => $category->id],
                    ['display_order' => $i, 'is_highlight' => in_array($amenityName, ['温泉', '露天風呂', '無料Wi-Fi', '駐車場（無料）'])]
                );
            }
        }
    }

    private function seedCancellationPolicies(): void
    {
        $policies = [
            [
                'code' => 'free_cancellation',
                'name' => '無料キャンセル可',
                'description' => '宿泊日の3日前までキャンセル無料',
                'rules' => [
                    ['days_before' => 3, 'charge_percent' => 0],
                    ['days_before' => 1, 'charge_percent' => 50],
                    ['days_before' => 0, 'charge_percent' => 100],
                ],
            ],
            [
                'code' => 'flexible',
                'name' => '柔軟なキャンセル',
                'description' => '宿泊日の前日までキャンセル可能',
                'rules' => [
                    ['days_before' => 1, 'charge_percent' => 0],
                    ['days_before' => 0, 'charge_percent' => 100],
                ],
            ],
            [
                'code' => 'moderate',
                'name' => '通常キャンセル',
                'description' => '宿泊日の7日前までキャンセル無料',
                'rules' => [
                    ['days_before' => 7, 'charge_percent' => 0],
                    ['days_before' => 3, 'charge_percent' => 30],
                    ['days_before' => 1, 'charge_percent' => 50],
                    ['days_before' => 0, 'charge_percent' => 100],
                ],
            ],
            [
                'code' => 'non_refundable',
                'name' => '返金不可',
                'description' => 'キャンセル・変更不可のお得なプラン',
                'rules' => [
                    ['days_before' => 999, 'charge_percent' => 100],
                ],
            ],
        ];

        foreach ($policies as $policy) {
            CancellationPolicy::firstOrCreate(
                ['code' => $policy['code']],
                ['name' => $policy['name'], 'description' => $policy['description'], 'rules' => $policy['rules']]
            );
        }
    }

    private function seedMemberRanks(): void
    {
        $ranks = [
            ['code' => 'regular', 'name' => 'レギュラー', 'min_spending' => 0, 'point_rate' => 0.01, 'color' => '#9e9e9e'],
            ['code' => 'silver', 'name' => 'シルバー', 'min_spending' => 50000, 'point_rate' => 0.02, 'color' => '#b0bec5'],
            ['code' => 'gold', 'name' => 'ゴールド', 'min_spending' => 150000, 'point_rate' => 0.03, 'color' => '#ffc107'],
            ['code' => 'platinum', 'name' => 'プラチナ', 'min_spending' => 300000, 'point_rate' => 0.05, 'color' => '#607d8b'],
        ];

        foreach ($ranks as $i => $rank) {
            MemberRank::firstOrCreate(
                ['code' => $rank['code']],
                array_merge($rank, ['display_order' => $i])
            );
        }
    }

    private function seedSampleAccommodations(): void
    {
        $tokyo = Area::where('name', '東京都')->first();
        $shibuya = Area::where('name', '渋谷区')->first();
        $atami = Area::where('name', '熱海市')->first();
        $kyoto = Area::where('name', '京都市')->first();

        $freePolicy = CancellationPolicy::where('code', 'free_cancellation')->first();

        $accommodations = [
            [
                'name' => 'ホテルグランドパレス東京',
                'facility_type' => 'hotel',
                'address' => '東京都渋谷区神宮前1-2-3',
                'phone' => '03-1234-5678',
                'description' => '渋谷駅から徒歩5分の好立地。ビジネスにも観光にも最適なシティホテルです。',
                'description_long' => '渋谷駅から徒歩5分、原宿・表参道へも徒歩圏内の好立地。全室Wi-Fi完備、朝食バイキングが人気のシティホテルです。',
                'area_id' => $shibuya?->id ?? $tokyo?->id,
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'latitude' => 35.6612,
                'longitude' => 139.7034,
                'star_rating' => 4,
                'review_score' => 4.3,
                'review_count' => 256,
                'min_price' => 8500,
                'highlight_features' => ['無料Wi-Fi', '朝食バイキング', '駅近'],
                'is_featured' => true,
                'cancellation_policy_id' => $freePolicy?->id,
            ],
            [
                'name' => '熱海温泉 海望旅館',
                'facility_type' => 'ryokan',
                'address' => '静岡県熱海市和田浜南町1-2-3',
                'phone' => '0557-12-3456',
                'description' => '相模湾を一望できる露天風呂が自慢の温泉旅館です。',
                'description_long' => '創業100年の老舗温泉旅館。全室オーシャンビュー、源泉掛け流しの露天風呂、地元の新鮮な海の幸を使った会席料理をお楽しみください。',
                'area_id' => $atami?->id,
                'check_in_time' => '15:00',
                'check_out_time' => '10:00',
                'latitude' => 35.0967,
                'longitude' => 139.0711,
                'star_rating' => 5,
                'review_score' => 4.7,
                'review_count' => 189,
                'min_price' => 18000,
                'highlight_features' => ['温泉', '露天風呂', 'オーシャンビュー', '会席料理'],
                'is_featured' => true,
                'cancellation_policy_id' => $freePolicy?->id,
            ],
            [
                'name' => '京都 町家ホテル 雅',
                'facility_type' => 'ryokan',
                'address' => '京都府京都市東山区祇園町南側1-2-3',
                'phone' => '075-123-4567',
                'description' => '祇園の中心に佇む町家を改装した和モダンホテル。',
                'description_long' => '築100年の町家をリノベーション。伝統とモダンが融合した空間で、京都ならではの滞在をお楽しみください。八坂神社、清水寺へも徒歩圏内。',
                'area_id' => $kyoto?->id,
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'latitude' => 35.0037,
                'longitude' => 135.7785,
                'star_rating' => 4,
                'review_score' => 4.5,
                'review_count' => 312,
                'min_price' => 15000,
                'highlight_features' => ['町家', '祇園', '和モダン'],
                'is_featured' => true,
                'cancellation_policy_id' => $freePolicy?->id,
            ],
        ];

        foreach ($accommodations as $data) {
            $accommodation = Accommodation::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            // 部屋とプランを作成
            $this->createRoomsAndPlans($accommodation);
        }
    }

    private function createRoomsAndPlans(Accommodation $accommodation): void
    {
        $roomTypes = [
            ['type' => 'standard', 'name' => 'スタンダードツイン', 'capacity' => 2, 'price' => $accommodation->min_price ?? 10000, 'sqm' => 22],
            ['type' => 'deluxe', 'name' => 'デラックスダブル', 'capacity' => 2, 'price' => ($accommodation->min_price ?? 10000) * 1.3, 'sqm' => 28],
            ['type' => 'suite', 'name' => 'スイートルーム', 'capacity' => 4, 'price' => ($accommodation->min_price ?? 10000) * 2, 'sqm' => 45],
        ];

        $freePolicy = CancellationPolicy::where('code', 'free_cancellation')->first();

        foreach ($roomTypes as $i => $roomData) {
            $room = Room::firstOrCreate(
                ['accommodation_id' => $accommodation->id, 'room_number' => sprintf('%d0%d', $accommodation->id, $i + 1)],
                [
                    'room_type' => $roomData['type'],
                    'room_type_name' => $roomData['name'],
                    'price_per_night' => $roomData['price'],
                    'capacity' => $roomData['capacity'],
                    'max_occupancy' => $roomData['capacity'] + 1,
                    'square_meters' => $roomData['sqm'],
                    'bed_type' => $roomData['type'] === 'standard' ? 'twin' : 'double',
                    'is_available' => true,
                    'room_amenities' => ['エアコン', 'テレビ', '冷蔵庫', 'Wi-Fi'],
                ]
            );

            // プラン作成
            $plans = [
                ['name' => '素泊まりプラン', 'meal' => 'room_only', 'price' => $roomData['price']],
                ['name' => '朝食付きプラン', 'meal' => 'breakfast_only', 'price' => $roomData['price'] + 1500],
            ];

            if ($accommodation->facility_type === 'ryokan') {
                $plans[] = ['name' => '1泊2食付きプラン', 'meal' => 'half_board', 'price' => $roomData['price'] + 5000];
            }

            foreach ($plans as $planData) {
                $plan = RoomPlan::firstOrCreate(
                    ['room_id' => $room->id, 'name' => $planData['name']],
                    [
                        'meal_type' => $planData['meal'],
                        'base_price' => $planData['price'],
                        'min_nights' => 1,
                        'max_guests' => $room->max_occupancy,
                        'cancellation_policy_id' => $freePolicy?->id,
                        'point_rate' => 0.02,
                        'is_active' => true,
                    ]
                );

                // 在庫作成（30日分）
                $startDate = Carbon::today();
                for ($d = 0; $d < 30; $d++) {
                    $date = $startDate->copy()->addDays($d);
                    PlanInventory::firstOrCreate(
                        ['room_plan_id' => $plan->id, 'date' => $date->format('Y-m-d')],
                        [
                            'total_inventory' => 5,
                            'available_inventory' => rand(1, 5),
                            'is_closed' => false,
                        ]
                    );
                }
            }
        }
    }
}
