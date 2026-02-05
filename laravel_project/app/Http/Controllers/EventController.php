<?php

namespace App\Http\Controllers;

use App\Services\EventSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    protected EventSearchService $eventService;

    public function __construct(EventSearchService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * イベント検索トップページ
     */
    public function index()
    {
        $featuredEvents = $this->eventService->getFeaturedEvents();
        $popularRegions = $this->eventService->getPopularRegions();
        $categories = $this->eventService->getCategories();
        $prefectures = $this->eventService->getPrefectures();

        return view('events.index', compact(
            'featuredEvents',
            'popularRegions',
            'categories',
            'prefectures'
        ));
    }

    /**
     * イベント検索結果
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'region' => 'required|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category' => 'nullable|string',
        ]);

        $region = $validated['region'];
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $category = $validated['category'] ?? null;

        // イベント検索
        $events = $this->eventService->searchEvents($region, $startDate, $endDate, $category);

        // カテゴリとプレフェクチャを取得
        $categories = $this->eventService->getCategories();
        $prefectures = $this->eventService->getPrefectures();

        // 検索条件
        $searchParams = [
            'region' => $region,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'category' => $category,
        ];

        return view('events.search', compact(
            'events',
            'categories',
            'prefectures',
            'searchParams'
        ));
    }

    /**
     * イベント詳細
     */
    public function show(string $id)
    {
        // キャッシュからイベント情報を検索
        $event = null;

        // すべてのキャッシュされたイベントから検索
        $cacheKeys = Cache::get('event_cache_keys', []);

        foreach ($cacheKeys as $key) {
            $events = Cache::get($key, []);
            foreach ($events as $e) {
                if (($e['id'] ?? null) === $id) {
                    $event = $e;
                    break 2;
                }
            }
        }

        // 見つからない場合はサンプルデータから検索
        if (!$event) {
            $sampleRegions = ['東京', '大阪', '京都', '北海道', '沖縄'];
            foreach ($sampleRegions as $region) {
                $events = $this->eventService->searchEvents($region);
                foreach ($events as $e) {
                    if (($e['id'] ?? null) === $id) {
                        $event = $e;
                        break 2;
                    }
                }
            }
        }

        if (!$event) {
            abort(404, 'イベントが見つかりません');
        }

        // 関連イベント（同じカテゴリ）
        $relatedEvents = [];
        if (isset($event['category'])) {
            $allEvents = $this->eventService->searchEvents($event['address'] ?? '東京');
            $relatedEvents = array_filter($allEvents, function ($e) use ($event, $id) {
                return ($e['category'] ?? '') === $event['category'] && ($e['id'] ?? '') !== $id;
            });
            $relatedEvents = array_slice($relatedEvents, 0, 4);
        }

        $categories = $this->eventService->getCategories();

        return view('events.show', compact('event', 'relatedEvents', 'categories'));
    }

    /**
     * イベントをお気に入りに追加
     */
    public function addFavorite(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'event_data' => 'required|array',
        ]);

        $userId = auth()->id() ?? session()->getId();
        $favorites = Cache::get("event_favorites_{$userId}", []);

        $favorites[$request->event_id] = $request->event_data;

        Cache::put("event_favorites_{$userId}", $favorites, 86400 * 30); // 30日間保存

        return response()->json(['success' => true, 'message' => 'お気に入りに追加しました']);
    }

    /**
     * お気に入りから削除
     */
    public function removeFavorite(string $eventId)
    {
        $userId = auth()->id() ?? session()->getId();
        $favorites = Cache::get("event_favorites_{$userId}", []);

        unset($favorites[$eventId]);

        Cache::put("event_favorites_{$userId}", $favorites, 86400 * 30);

        return response()->json(['success' => true, 'message' => 'お気に入りから削除しました']);
    }

    /**
     * お気に入り一覧
     */
    public function favorites()
    {
        $userId = auth()->id() ?? session()->getId();
        $favorites = Cache::get("event_favorites_{$userId}", []);
        $categories = $this->eventService->getCategories();

        return view('events.favorites', compact('favorites', 'categories'));
    }

    /**
     * カレンダー表示
     */
    public function calendar(Request $request)
    {
        $region = $request->get('region', '東京');
        $month = $request->get('month', date('Y-m'));

        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $events = $this->eventService->searchEvents($region, $startDate, $endDate);

        // カレンダー用にイベントを日付でグループ化
        $eventsByDate = [];
        foreach ($events as $event) {
            $date = $event['start_date'];
            if (!isset($eventsByDate[$date])) {
                $eventsByDate[$date] = [];
            }
            $eventsByDate[$date][] = $event;
        }

        $categories = $this->eventService->getCategories();
        $prefectures = $this->eventService->getPrefectures();

        return view('events.calendar', compact(
            'events',
            'eventsByDate',
            'region',
            'month',
            'categories',
            'prefectures'
        ));
    }

    /**
     * API: イベント検索
     */
    public function apiSearch(Request $request)
    {
        $validated = $request->validate([
            'region' => 'required|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'category' => 'nullable|string',
        ]);

        $events = $this->eventService->searchEvents(
            $validated['region'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            $validated['category'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $events,
            'count' => count($events),
        ]);
    }
}
