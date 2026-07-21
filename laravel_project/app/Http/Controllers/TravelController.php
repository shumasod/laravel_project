<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Area;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\Reservation;
use App\Models\RoomPlan;
use App\Models\Customer;
use App\Services\TravelSearchService;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TravelController extends Controller
{
    public function __construct(
        private TravelSearchService $searchService,
        private ReservationService $reservationService
    ) {}

    /**
     * トップページ
     */
    public function index()
    {
        $popularAreas = $this->searchService->getPopularAreas();
        $featuredAccommodations = $this->searchService->getFeaturedAccommodations();

        $prefectures = Area::prefectures()
            ->orderBy('display_order')
            ->get(['id', 'name']);

        return view('travel.index', compact(
            'popularAreas',
            'featuredAccommodations',
            'prefectures'
        ));
    }

    /**
     * 検索結果
     */
    public function search(Request $request)
    {
        $params = $request->validate([
            'keyword' => 'nullable|string|max:200',
            'area_id' => 'nullable|integer|exists:areas,id',
            'prefecture' => 'nullable|string|max:50',
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
            'guests' => 'nullable|integer|min:1|max:20',
            'rooms' => 'nullable|integer|min:1|max:10',
            'facility_type' => 'nullable|array',
            'facility_type.*' => 'string',
            'meal_type' => 'nullable|string',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
            'star_rating' => 'nullable|array',
            'star_rating.*' => 'integer|min:1|max:5',
            'min_review_score' => 'nullable|numeric|min:0|max:5',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer',
            'sort' => 'nullable|string|in:recommended,price_asc,price_desc,rating_desc,review_count_desc,distance_asc,newest',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:10|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $customerId = auth()->id();
        $sessionId = session()->getId();

        $results = $this->searchService->search($params, $customerId, $sessionId);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $results,
            ]);
        }

        return view('travel.search', [
            'results' => $results,
            'params' => $params,
        ]);
    }

    /**
     * 施設詳細
     */
    public function show(Request $request, int $id)
    {
        $customerId = auth()->id();
        $sessionId = session()->getId();

        $accommodation = $this->searchService->getAccommodationDetail($id, $customerId, $sessionId);

        if (!$accommodation) {
            abort(404);
        }

        // プラン検索パラメータがある場合
        $plans = [];
        if ($request->has('check_in') && $request->has('check_out')) {
            $plans = $this->searchService->searchPlans($id, $request->only([
                'check_in', 'check_out', 'guests', 'rooms'
            ]));
        }

        // 口コミ取得
        $reviews = Review::with('customer')
            ->where('accommodation_id', $id)
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // お気に入りチェック
        $isFavorite = false;
        if ($customerId) {
            $isFavorite = Favorite::where('customer_id', $customerId)
                ->where('accommodation_id', $id)
                ->exists();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => array_merge($accommodation, [
                    'plans' => $plans,
                    'reviews' => $reviews,
                    'is_favorite' => $isFavorite,
                ]),
            ]);
        }

        return view('travel.show', compact(
            'accommodation',
            'plans',
            'reviews',
            'isFavorite'
        ));
    }

    /**
     * プラン検索API
     */
    public function searchPlans(Request $request, int $id): JsonResponse
    {
        $params = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1|max:20',
            'rooms' => 'nullable|integer|min:1|max:10',
        ]);

        $plans = $this->searchService->searchPlans($id, $params);

        return response()->json([
            'status' => 'success',
            'data' => $plans,
        ]);
    }

    /**
     * 予約入力画面
     */
    public function booking(Request $request, int $planId)
    {
        $plan = RoomPlan::with(['room.accommodation', 'cancellationPolicy'])->findOrFail($planId);

        $params = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'rooms' => 'nullable|integer|min:1',
        ]);

        $checkIn = Carbon::parse($params['check_in']);
        $checkOut = Carbon::parse($params['check_out']);
        $nights = $checkIn->diffInDays($checkOut);

        // 料金計算
        $totalPrice = $plan->base_price * $params['guests'] * ($params['rooms'] ?? 1) * $nights;

        return view('travel.booking', [
            'plan' => $plan,
            'accommodation' => $plan->room->accommodation,
            'params' => $params,
            'nights' => $nights,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * 予約確認画面
     */
    public function confirmBooking(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:room_plans,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'rooms' => 'nullable|integer|min:1',
            'name' => 'required|string|max:100',
            'name_kana' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'arrival_time' => 'required|string',
            'special_requests' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:on_site,credit_card',
        ]);

        $plan = RoomPlan::with(['room.accommodation'])->findOrFail($validated['plan_id']);

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalPrice = $plan->base_price * $validated['guests'] * ($validated['rooms'] ?? 1) * $nights;

        return view('travel.confirm', [
            'plan' => $plan,
            'accommodation' => $plan->room->accommodation,
            'booking' => $validated,
            'nights' => $nights,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * 予約完了
     */
    public function completeBooking(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:room_plans,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'rooms' => 'nullable|integer|min:1',
            'name' => 'required|string|max:100',
            'name_kana' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'arrival_time' => 'required|string',
            'special_requests' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:on_site,credit_card',
        ]);

        $plan = RoomPlan::with(['room'])->findOrFail($validated['plan_id']);

        // 顧客を取得または作成
        $customer = Customer::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'privacy_consent' => true,
                'privacy_consent_date' => now(),
            ]
        );

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);

        // 予約作成
        $reservation = $this->reservationService->createReservation([
            'room_id' => $plan->room_id,
            'customer_id' => $customer->id,
            'check_in_date' => $validated['check_in'],
            'check_out_date' => $validated['check_out'],
            'number_of_guests' => $validated['guests'],
            'room_plan_id' => $plan->id,
            'arrival_time' => $validated['arrival_time'],
            'special_requests' => $validated['special_requests'],
            'booking_source' => 'web',
        ]);

        return view('travel.complete', [
            'reservation' => $reservation,
            'accommodation' => $plan->room->accommodation,
        ]);
    }

    /**
     * お気に入り追加
     */
    public function addFavorite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
        ]);

        $customerId = auth()->id();
        if (!$customerId) {
            return response()->json(['status' => 'error', 'message' => 'ログインが必要です'], 401);
        }

        Favorite::firstOrCreate([
            'customer_id' => $customerId,
            'accommodation_id' => $validated['accommodation_id'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'お気に入りに追加しました']);
    }

    /**
     * お気に入り削除
     */
    public function removeFavorite(Request $request, int $accommodationId): JsonResponse
    {
        $customerId = auth()->id();
        if (!$customerId) {
            return response()->json(['status' => 'error', 'message' => 'ログインが必要です'], 401);
        }

        Favorite::where('customer_id', $customerId)
            ->where('accommodation_id', $accommodationId)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'お気に入りから削除しました']);
    }

    /**
     * サジェストAPI
     */
    public function suggest(Request $request): JsonResponse
    {
        $request->validate(['q' => 'nullable|string|max:200']);

        $keyword = $request->input('q', '');

        if (mb_strlen($keyword) < 1) {
            return response()->json(['data' => []]);
        }

        $results = $this->searchService->suggest($keyword);

        return response()->json(['data' => $results]);
    }

    /**
     * エリア一覧API
     */
    public function areas(Request $request): JsonResponse
    {
        $request->validate([
            'parent_id' => 'nullable|integer|exists:areas,id',
            'level'     => 'nullable|integer|min:1|max:5',
        ]);

        $parentId = $request->integer('parent_id') ?: null;
        $level = $request->integer('level') ?: null;

        $query = Area::query();

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } elseif ($level) {
            $query->where('level', $level);
        }

        $areas = $query->orderBy('display_order')->get(['id', 'name', 'level', 'accommodation_count']);

        return response()->json(['data' => $areas]);
    }

    /**
     * 口コミ一覧API
     */
    public function reviews(Request $request, int $accommodationId): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $sort = $request->input('sort', 'newest');

        $query = Review::with('customer:id,name')
            ->where('accommodation_id', $accommodationId)
            ->where('is_published', true);

        switch ($sort) {
            case 'rating_high':
                $query->orderBy('overall_rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('overall_rating', 'asc');
                break;
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $reviews = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $reviews,
        ]);
    }
}
