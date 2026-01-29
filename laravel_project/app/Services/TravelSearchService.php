<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Area;
use App\Models\RoomPlan;
use App\Models\PlanInventory;
use App\Models\SearchHistory;
use App\Models\ViewHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TravelSearchService
{
    /**
     * 検索パラメータのデフォルト値
     */
    private array $defaultParams = [
        'per_page' => 20,
        'sort' => 'recommended',
        'price_display' => 'per_person',
    ];

    /**
     * ソート条件のマッピング
     */
    private array $sortMapping = [
        'recommended' => ['display_priority', 'desc'],
        'price_asc' => ['min_price', 'asc'],
        'price_desc' => ['min_price', 'desc'],
        'rating_desc' => ['review_score', 'desc'],
        'review_count_desc' => ['review_count', 'desc'],
        'newest' => ['created_at', 'desc'],
    ];

    /**
     * 宿泊施設を検索
     */
    public function search(array $params, ?int $customerId = null, ?string $sessionId = null): array
    {
        $params = array_merge($this->defaultParams, $params);

        // 検索クエリを構築
        $query = Accommodation::query()
            ->with(['photos' => fn($q) => $q->orderBy('is_main', 'desc')->limit(5)])
            ->with('area');

        // 基本検索条件
        $this->applyBasicFilters($query, $params);

        // 詳細フィルター
        $this->applyAdvancedFilters($query, $params);

        // 空室チェック（日付指定時）
        if (!empty($params['check_in']) && !empty($params['check_out'])) {
            $this->applyAvailabilityFilter($query, $params);
        }

        // ソート
        $this->applySort($query, $params['sort'], $params);

        // ページネーション
        $results = $query->paginate($params['per_page']);

        // 検索履歴を保存
        $this->saveSearchHistory($params, $results->total(), $customerId, $sessionId);

        // レスポンス整形
        return $this->formatSearchResults($results, $params);
    }

    /**
     * 基本検索条件を適用
     */
    private function applyBasicFilters($query, array $params): void
    {
        // キーワード検索
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%")
                  ->orWhereHas('area', fn($aq) => $aq->where('name', 'like', "%{$keyword}%"));
            });
        }

        // エリア検索
        if (!empty($params['area_id'])) {
            $areaIds = $this->getAreaWithChildren($params['area_id']);
            $query->whereIn('area_id', $areaIds);
        }

        // 都道府県検索
        if (!empty($params['prefecture'])) {
            $query->whereHas('area', function ($q) use ($params) {
                $q->where('name', 'like', "%{$params['prefecture']}%")
                  ->where('level', 'prefecture');
            });
        }
    }

    /**
     * 詳細フィルターを適用
     */
    private function applyAdvancedFilters($query, array $params): void
    {
        // 施設タイプ
        if (!empty($params['facility_type'])) {
            $types = is_array($params['facility_type']) ? $params['facility_type'] : [$params['facility_type']];
            $query->whereIn('facility_type', $types);
        }

        // 価格帯
        if (!empty($params['min_price'])) {
            $query->where('min_price', '>=', $params['min_price']);
        }
        if (!empty($params['max_price'])) {
            $query->where(function ($q) use ($params) {
                $q->where('min_price', '<=', $params['max_price'])
                  ->orWhereNull('min_price');
            });
        }

        // 星評価
        if (!empty($params['star_rating'])) {
            $ratings = is_array($params['star_rating']) ? $params['star_rating'] : [$params['star_rating']];
            $query->whereIn('star_rating', $ratings);
        }

        // 口コミ評価
        if (!empty($params['min_review_score'])) {
            $query->where('review_score', '>=', $params['min_review_score']);
        }

        // アメニティ
        if (!empty($params['amenities'])) {
            $amenityIds = is_array($params['amenities']) ? $params['amenities'] : [$params['amenities']];
            foreach ($amenityIds as $amenityId) {
                $query->whereHas('amenities', fn($q) => $q->where('amenity_id', $amenityId));
            }
        }

        // 食事条件
        if (!empty($params['meal_type'])) {
            $query->whereHas('roomPlans', fn($q) => $q->where('meal_type', $params['meal_type']));
        }

        // おすすめ・特集
        if (!empty($params['is_featured'])) {
            $query->where('is_featured', true);
        }
    }

    /**
     * 空室フィルターを適用
     */
    private function applyAvailabilityFilter($query, array $params): void
    {
        $checkIn = Carbon::parse($params['check_in']);
        $checkOut = Carbon::parse($params['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $guests = $params['guests'] ?? 2;
        $rooms = $params['rooms'] ?? 1;

        $query->whereHas('rooms.plans', function ($q) use ($checkIn, $checkOut, $nights, $guests, $rooms) {
            $q->where('is_active', true)
              ->where('max_guests', '>=', $guests)
              ->whereHas('inventories', function ($iq) use ($checkIn, $nights, $rooms) {
                  $iq->whereBetween('date', [$checkIn, $checkIn->copy()->addDays($nights - 1)])
                     ->where('available_inventory', '>=', $rooms)
                     ->where('is_closed', false);
              }, '=', $nights);
        });
    }

    /**
     * ソートを適用
     */
    private function applySort($query, string $sort, array $params): void
    {
        // 距離ソート（緯度経度指定時）
        if ($sort === 'distance_asc' && !empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                  ->orderBy('distance', 'asc');
            return;
        }

        // 通常のソート
        if (isset($this->sortMapping[$sort])) {
            [$column, $direction] = $this->sortMapping[$sort];
            $query->orderBy($column, $direction);
        }

        // セカンダリソート
        $query->orderBy('id', 'desc');
    }

    /**
     * エリアとその子エリアのIDを取得
     */
    private function getAreaWithChildren(int $areaId): array
    {
        return Cache::remember("area_children_{$areaId}", 3600, function () use ($areaId) {
            $ids = [$areaId];
            $children = Area::where('parent_id', $areaId)->pluck('id')->toArray();
            $ids = array_merge($ids, $children);

            // 孫エリアも含める
            if (!empty($children)) {
                $grandchildren = Area::whereIn('parent_id', $children)->pluck('id')->toArray();
                $ids = array_merge($ids, $grandchildren);
            }

            return $ids;
        });
    }

    /**
     * 検索履歴を保存
     */
    private function saveSearchHistory(array $params, int $resultCount, ?int $customerId, ?string $sessionId): void
    {
        if (!$customerId && !$sessionId) {
            return;
        }

        SearchHistory::create([
            'customer_id' => $customerId,
            'session_id' => $sessionId,
            'search_params' => $params,
            'result_count' => $resultCount,
        ]);
    }

    /**
     * 検索結果を整形
     */
    private function formatSearchResults(LengthAwarePaginator $results, array $params): array
    {
        return [
            'items' => $results->map(fn($item) => $this->formatAccommodation($item, $params)),
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
            ],
            'filters' => $this->getAvailableFilters($params),
        ];
    }

    /**
     * 施設情報を整形
     */
    private function formatAccommodation(Accommodation $accommodation, array $params): array
    {
        $mainPhoto = $accommodation->photos->firstWhere('is_main', true) ?? $accommodation->photos->first();

        return [
            'id' => $accommodation->id,
            'name' => $accommodation->name,
            'facility_type' => $accommodation->facility_type,
            'address' => $accommodation->address,
            'area_name' => $accommodation->area?->name,
            'access' => $accommodation->nearest_station_id
                ? "{$accommodation->nearestStation?->name}駅から徒歩{$accommodation->station_distance_minutes}分"
                : null,
            'star_rating' => $accommodation->star_rating,
            'review_score' => $accommodation->review_score,
            'review_count' => $accommodation->review_count,
            'min_price' => $accommodation->min_price,
            'main_photo' => $mainPhoto?->url,
            'photos' => $accommodation->photos->take(5)->pluck('thumbnail_url'),
            'highlight_features' => $accommodation->highlight_features ?? [],
            'is_featured' => $accommodation->is_featured,
            'latitude' => $accommodation->latitude,
            'longitude' => $accommodation->longitude,
        ];
    }

    /**
     * 利用可能なフィルターを取得
     */
    private function getAvailableFilters(array $params): array
    {
        return [
            'facility_types' => [
                ['value' => 'hotel', 'label' => 'ホテル'],
                ['value' => 'ryokan', 'label' => '旅館'],
                ['value' => 'resort', 'label' => 'リゾートホテル'],
                ['value' => 'minshuku', 'label' => '民宿'],
                ['value' => 'guesthouse', 'label' => 'ゲストハウス'],
                ['value' => 'vacation_rental', 'label' => '貸別荘'],
                ['value' => 'capsule', 'label' => 'カプセルホテル'],
            ],
            'meal_types' => [
                ['value' => 'room_only', 'label' => '素泊まり'],
                ['value' => 'breakfast_only', 'label' => '朝食付き'],
                ['value' => 'half_board', 'label' => '1泊2食付き'],
            ],
            'price_ranges' => [
                ['min' => 0, 'max' => 5000, 'label' => '〜5,000円'],
                ['min' => 5000, 'max' => 10000, 'label' => '5,000〜10,000円'],
                ['min' => 10000, 'max' => 20000, 'label' => '10,000〜20,000円'],
                ['min' => 20000, 'max' => 50000, 'label' => '20,000〜50,000円'],
                ['min' => 50000, 'max' => null, 'label' => '50,000円〜'],
            ],
            'star_ratings' => [5, 4, 3, 2, 1],
            'review_scores' => [4.5, 4.0, 3.5, 3.0],
        ];
    }

    /**
     * 施設詳細を取得
     */
    public function getAccommodationDetail(int $id, ?int $customerId = null, ?string $sessionId = null): ?array
    {
        $accommodation = Accommodation::with([
            'photos' => fn($q) => $q->orderBy('is_main', 'desc')->orderBy('display_order'),
            'amenities.category',
            'area.parent',
            'nearestStation',
            'rooms.plans' => fn($q) => $q->where('is_active', true),
            'cancellationPolicy',
        ])->find($id);

        if (!$accommodation) {
            return null;
        }

        // 閲覧履歴を保存
        $this->saveViewHistory($id, $customerId, $sessionId);

        return $this->formatAccommodationDetail($accommodation);
    }

    /**
     * 閲覧履歴を保存
     */
    private function saveViewHistory(int $accommodationId, ?int $customerId, ?string $sessionId): void
    {
        if (!$customerId && !$sessionId) {
            return;
        }

        ViewHistory::updateOrCreate(
            [
                'customer_id' => $customerId,
                'accommodation_id' => $accommodationId,
            ],
            [
                'session_id' => $sessionId,
                'view_count' => DB::raw('view_count + 1'),
                'last_viewed_at' => now(),
            ]
        );
    }

    /**
     * 施設詳細を整形
     */
    private function formatAccommodationDetail(Accommodation $accommodation): array
    {
        return [
            'id' => $accommodation->id,
            'name' => $accommodation->name,
            'facility_type' => $accommodation->facility_type,
            'description' => $accommodation->description,
            'description_long' => $accommodation->description_long,
            'address' => $accommodation->address,
            'phone' => $accommodation->phone,
            'email' => $accommodation->email,
            'check_in_time' => $accommodation->check_in_time,
            'check_out_time' => $accommodation->check_out_time,
            'latitude' => $accommodation->latitude,
            'longitude' => $accommodation->longitude,
            'area' => [
                'id' => $accommodation->area?->id,
                'name' => $accommodation->area?->name,
                'path' => $accommodation->area?->path,
            ],
            'access' => [
                'station' => $accommodation->nearestStation?->name,
                'station_distance' => $accommodation->station_distance_minutes,
                'info' => $accommodation->access_info,
            ],
            'ratings' => [
                'star' => $accommodation->star_rating,
                'review_score' => $accommodation->review_score,
                'review_count' => $accommodation->review_count,
                'cleanliness' => $accommodation->cleanliness_score,
                'service' => $accommodation->service_score,
                'location' => $accommodation->location_score,
                'facility' => $accommodation->facility_score,
                'value' => $accommodation->value_score,
            ],
            'photos' => $accommodation->photos->map(fn($p) => [
                'url' => $p->url,
                'thumbnail' => $p->thumbnail_url,
                'caption' => $p->caption,
                'category' => $p->category,
                'is_main' => $p->is_main,
            ]),
            'amenities' => $accommodation->amenities->groupBy('category.name')->map(fn($items) =>
                $items->pluck('name')
            ),
            'highlight_features' => $accommodation->highlight_features ?? [],
            'parking' => $accommodation->parking_info,
            'cancellation_policy' => $accommodation->cancellationPolicy?->only(['name', 'description', 'rules']),
            'rooms' => $accommodation->rooms->map(fn($room) => [
                'id' => $room->id,
                'name' => $room->room_type_name ?? $room->room_type,
                'capacity' => $room->capacity,
                'max_occupancy' => $room->max_occupancy,
                'square_meters' => $room->square_meters,
                'bed_type' => $room->bed_type,
                'image' => $room->main_image_url,
                'amenities' => $room->room_amenities,
                'plans' => $room->plans->map(fn($plan) => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'meal_type' => $plan->meal_type,
                    'base_price' => $plan->base_price,
                    'benefits' => $plan->benefits,
                    'point_rate' => $plan->point_rate,
                ]),
            ]),
        ];
    }

    /**
     * プラン検索（日付・人数指定）
     */
    public function searchPlans(int $accommodationId, array $params): array
    {
        $checkIn = Carbon::parse($params['check_in']);
        $checkOut = Carbon::parse($params['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $guests = $params['guests'] ?? 2;
        $rooms = $params['rooms'] ?? 1;

        $plans = RoomPlan::with(['room', 'cancellationPolicy'])
            ->whereHas('room', fn($q) => $q->where('accommodation_id', $accommodationId))
            ->where('is_active', true)
            ->where('max_guests', '>=', $guests)
            ->whereHas('inventories', function ($q) use ($checkIn, $nights, $rooms) {
                $q->whereBetween('date', [$checkIn, $checkIn->copy()->addDays($nights - 1)])
                  ->where('available_inventory', '>=', $rooms)
                  ->where('is_closed', false);
            }, '=', $nights)
            ->orderBy('base_price')
            ->get();

        return $plans->map(function ($plan) use ($checkIn, $checkOut, $nights, $guests, $rooms) {
            $totalPrice = $this->calculatePlanPrice($plan, $checkIn, $nights, $guests, $rooms);

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'room' => [
                    'id' => $plan->room->id,
                    'name' => $plan->room->room_type_name,
                    'capacity' => $plan->room->capacity,
                    'image' => $plan->room->main_image_url,
                ],
                'meal_type' => $plan->meal_type,
                'meal_description' => $plan->meal_description,
                'base_price' => $plan->base_price,
                'total_price' => $totalPrice,
                'price_per_person' => (int) ($totalPrice / $guests / $rooms),
                'benefits' => $plan->benefits,
                'point_rate' => $plan->point_rate,
                'cancellation_policy' => $plan->cancellationPolicy?->name,
            ];
        })->toArray();
    }

    /**
     * プラン料金を計算
     */
    private function calculatePlanPrice(RoomPlan $plan, Carbon $checkIn, int $nights, int $guests, int $rooms): int
    {
        $total = 0;

        for ($i = 0; $i < $nights; $i++) {
            $date = $checkIn->copy()->addDays($i);

            // 日付別料金があれば使用
            $inventory = PlanInventory::where('room_plan_id', $plan->id)
                ->where('date', $date->format('Y-m-d'))
                ->first();

            $dayPrice = $inventory?->price ?? $plan->base_price;
            $total += $dayPrice * $guests * $rooms;
        }

        return $total;
    }

    /**
     * サジェスト（オートコンプリート）
     */
    public function suggest(string $keyword, int $limit = 10): array
    {
        $results = [];

        // エリア検索
        $areas = Area::where('name', 'like', "{$keyword}%")
            ->orWhere('name_kana', 'like', "{$keyword}%")
            ->limit(5)
            ->get()
            ->map(fn($a) => ['type' => 'area', 'id' => $a->id, 'name' => $a->name, 'label' => $a->path]);

        // 施設検索
        $accommodations = Accommodation::where('name', 'like', "%{$keyword}%")
            ->limit(5)
            ->get()
            ->map(fn($a) => ['type' => 'accommodation', 'id' => $a->id, 'name' => $a->name, 'label' => $a->name]);

        return array_merge($areas->toArray(), $accommodations->toArray());
    }

    /**
     * 人気エリアを取得
     */
    public function getPopularAreas(int $limit = 12): array
    {
        return Cache::remember('popular_areas', 3600, function () use ($limit) {
            return Area::popular()
                ->orderBy('accommodation_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'accommodation_count' => $a->accommodation_count,
                ])
                ->toArray();
        });
    }

    /**
     * おすすめ施設を取得
     */
    public function getFeaturedAccommodations(int $limit = 8): array
    {
        return Accommodation::with(['photos' => fn($q) => $q->where('is_main', true)])
            ->where('is_featured', true)
            ->orderBy('display_priority', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($a) => $this->formatAccommodation($a, []))
            ->toArray();
    }
}
