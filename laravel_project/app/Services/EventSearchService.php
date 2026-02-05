<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventSearchService
{
    /**
     * イベントカテゴリ
     */
    const CATEGORIES = [
        'festival' => '祭り・フェスティバル',
        'concert' => 'コンサート・ライブ',
        'exhibition' => '展覧会・美術展',
        'sports' => 'スポーツイベント',
        'fireworks' => '花火大会',
        'food' => 'グルメ・フードフェス',
        'traditional' => '伝統行事',
        'illumination' => 'イルミネーション',
        'market' => 'マーケット・蚤の市',
        'other' => 'その他',
    ];

    /**
     * 都道府県リスト
     */
    const PREFECTURES = [
        '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
        '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
        '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
        '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
        '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
        '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
        '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県',
    ];

    /**
     * 地域からイベントを検索
     */
    public function searchEvents(string $region, ?string $startDate = null, ?string $endDate = null, ?string $category = null): array
    {
        $cacheKey = "events_{$region}_{$startDate}_{$endDate}_{$category}";

        // キャッシュから取得（1時間）
        return Cache::remember($cacheKey, 3600, function () use ($region, $startDate, $endDate, $category) {
            $events = [];

            // 複数のソースからイベント情報を収集
            $events = array_merge(
                $events,
                $this->searchFromWalkerPlus($region, $startDate, $endDate, $category),
                $this->searchFromJalan($region, $startDate, $endDate, $category),
                $this->generateSampleEvents($region, $startDate, $endDate, $category)
            );

            // 日付でソート
            usort($events, function ($a, $b) {
                return strtotime($a['start_date']) - strtotime($b['start_date']);
            });

            return $events;
        });
    }

    /**
     * Walker+からイベント情報を検索（シミュレート）
     */
    protected function searchFromWalkerPlus(string $region, ?string $startDate, ?string $endDate, ?string $category): array
    {
        $events = [];

        try {
            // Walker+ APIをシミュレート（実際のAPIがある場合はここで呼び出し）
            $query = urlencode("{$region} イベント " . ($startDate ? date('Y年m月', strtotime($startDate)) : ''));

            // 実際の実装では外部APIを呼び出す
            // $response = Http::get("https://api.walkerplus.com/events", [...]);

        } catch (\Exception $e) {
            Log::warning('Walker+ API error: ' . $e->getMessage());
        }

        return $events;
    }

    /**
     * じゃらんからイベント情報を検索（シミュレート）
     */
    protected function searchFromJalan(string $region, ?string $startDate, ?string $endDate, ?string $category): array
    {
        $events = [];

        try {
            // じゃらんイベントAPIをシミュレート
            // 実際の実装では外部APIを呼び出す

        } catch (\Exception $e) {
            Log::warning('Jalan API error: ' . $e->getMessage());
        }

        return $events;
    }

    /**
     * サンプルイベントデータを生成（デモ用）
     */
    protected function generateSampleEvents(string $region, ?string $startDate, ?string $endDate, ?string $category): array
    {
        $events = [];
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->addMonths(3);

        // 地域別のサンプルイベント
        $sampleEvents = $this->getSampleEventsForRegion($region);

        foreach ($sampleEvents as $event) {
            $eventDate = Carbon::parse($event['start_date']);

            // 日付範囲内かチェック
            if ($eventDate->between($start, $end)) {
                // カテゴリフィルター
                if ($category && $event['category'] !== $category) {
                    continue;
                }
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * 地域別サンプルイベント
     */
    protected function getSampleEventsForRegion(string $region): array
    {
        $baseEvents = [
            '東京' => [
                [
                    'id' => 'tokyo_1',
                    'title' => '東京マラソン2026',
                    'description' => '国内最大級の市民マラソン大会。都心を駆け抜ける42.195km。',
                    'start_date' => '2026-03-01',
                    'end_date' => '2026-03-01',
                    'venue' => '東京都庁前〜東京駅',
                    'address' => '東京都新宿区西新宿2丁目',
                    'category' => 'sports',
                    'image_url' => 'https://placehold.co/400x300/3498db/white?text=Tokyo+Marathon',
                    'url' => 'https://www.marathon.tokyo/',
                    'price' => '参加費16,500円',
                    'source' => 'サンプルデータ',
                ],
                [
                    'id' => 'tokyo_2',
                    'title' => '上野公園桜まつり',
                    'description' => '約800本の桜が咲き誇る上野恩賜公園での花見イベント。',
                    'start_date' => '2026-03-20',
                    'end_date' => '2026-04-10',
                    'venue' => '上野恩賜公園',
                    'address' => '東京都台東区上野公園',
                    'category' => 'festival',
                    'image_url' => 'https://placehold.co/400x300/ec407a/white?text=Sakura+Festival',
                    'url' => null,
                    'price' => '入場無料',
                    'source' => 'サンプルデータ',
                ],
                [
                    'id' => 'tokyo_3',
                    'title' => '東京ゲームショウ2026',
                    'description' => 'アジア最大級のゲーム展示会。最新ゲームを体験できる。',
                    'start_date' => '2026-09-24',
                    'end_date' => '2026-09-27',
                    'venue' => '幕張メッセ',
                    'address' => '千葉県千葉市美浜区中瀬2-1',
                    'category' => 'exhibition',
                    'image_url' => 'https://placehold.co/400x300/9c27b0/white?text=TGS+2026',
                    'url' => 'https://tgs.nikkeibp.co.jp/',
                    'price' => '当日2,500円',
                    'source' => 'サンプルデータ',
                ],
                [
                    'id' => 'tokyo_4',
                    'title' => '隅田川花火大会',
                    'description' => '約20,000発の花火が夜空を彩る、東京を代表する花火大会。',
                    'start_date' => '2026-07-25',
                    'end_date' => '2026-07-25',
                    'venue' => '隅田川沿い',
                    'address' => '東京都墨田区・台東区',
                    'category' => 'fireworks',
                    'image_url' => 'https://placehold.co/400x300/ff5722/white?text=Sumida+Fireworks',
                    'url' => null,
                    'price' => '観覧無料',
                    'source' => 'サンプルデータ',
                ],
            ],
            '大阪' => [
                [
                    'id' => 'osaka_1',
                    'title' => '天神祭',
                    'description' => '日本三大祭りの一つ。船渡御と奉納花火が見どころ。',
                    'start_date' => '2026-07-24',
                    'end_date' => '2026-07-25',
                    'venue' => '大阪天満宮周辺',
                    'address' => '大阪府大阪市北区天神橋2丁目',
                    'category' => 'festival',
                    'image_url' => 'https://placehold.co/400x300/e91e63/white?text=Tenjin+Matsuri',
                    'url' => null,
                    'price' => '観覧無料',
                    'source' => 'サンプルデータ',
                ],
                [
                    'id' => 'osaka_2',
                    'title' => '大阪マラソン2026',
                    'description' => '大阪の名所を巡るフルマラソン大会。',
                    'start_date' => '2026-02-22',
                    'end_date' => '2026-02-22',
                    'venue' => '大阪城公園〜インテックス大阪',
                    'address' => '大阪府大阪市中央区',
                    'category' => 'sports',
                    'image_url' => 'https://placehold.co/400x300/2196f3/white?text=Osaka+Marathon',
                    'url' => 'https://www.osaka-marathon.com/',
                    'price' => '参加費16,500円',
                    'source' => 'サンプルデータ',
                ],
            ],
            '京都' => [
                [
                    'id' => 'kyoto_1',
                    'title' => '祇園祭',
                    'description' => '日本三大祭りの一つ。7月の京都を彩る壮大な祭り。',
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-07-31',
                    'venue' => '八坂神社・四条通周辺',
                    'address' => '京都府京都市東山区祇園町',
                    'category' => 'festival',
                    'image_url' => 'https://placehold.co/400x300/ff9800/white?text=Gion+Matsuri',
                    'url' => null,
                    'price' => '観覧無料',
                    'source' => 'サンプルデータ',
                ],
                [
                    'id' => 'kyoto_2',
                    'title' => '京都紅葉ライトアップ',
                    'description' => '清水寺、永観堂など各所で紅葉のライトアップを開催。',
                    'start_date' => '2026-11-10',
                    'end_date' => '2026-12-05',
                    'venue' => '京都市内各所',
                    'address' => '京都府京都市',
                    'category' => 'illumination',
                    'image_url' => 'https://placehold.co/400x300/f44336/white?text=Kyoto+Autumn',
                    'url' => null,
                    'price' => '施設により異なる',
                    'source' => 'サンプルデータ',
                ],
            ],
            '北海道' => [
                [
                    'id' => 'hokkaido_1',
                    'title' => 'さっぽろ雪まつり',
                    'description' => '世界的に有名な雪と氷の祭典。大雪像が並ぶ。',
                    'start_date' => '2026-02-04',
                    'end_date' => '2026-02-11',
                    'venue' => '大通公園・すすきの・つどーむ',
                    'address' => '北海道札幌市中央区大通西',
                    'category' => 'festival',
                    'image_url' => 'https://placehold.co/400x300/00bcd4/white?text=Snow+Festival',
                    'url' => 'https://www.snowfes.com/',
                    'price' => '入場無料',
                    'source' => 'サンプルデータ',
                ],
                [
                    'id' => 'hokkaido_2',
                    'title' => '富良野ラベンダー祭り',
                    'description' => '一面に広がるラベンダー畑を楽しめる。',
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-07-31',
                    'venue' => 'ファーム富田',
                    'address' => '北海道空知郡中富良野町',
                    'category' => 'festival',
                    'image_url' => 'https://placehold.co/400x300/9c27b0/white?text=Lavender+Festival',
                    'url' => null,
                    'price' => '入場無料',
                    'source' => 'サンプルデータ',
                ],
            ],
            '沖縄' => [
                [
                    'id' => 'okinawa_1',
                    'title' => '那覇大綱挽まつり',
                    'description' => 'ギネス認定の世界一の大綱引き。',
                    'start_date' => '2026-10-10',
                    'end_date' => '2026-10-12',
                    'venue' => '国道58号線久茂地交差点',
                    'address' => '沖縄県那覇市久茂地',
                    'category' => 'festival',
                    'image_url' => 'https://placehold.co/400x300/ff5722/white?text=Naha+Festival',
                    'url' => null,
                    'price' => '参加無料',
                    'source' => 'サンプルデータ',
                ],
                [
                    'id' => 'okinawa_2',
                    'title' => '沖縄国際映画祭',
                    'description' => 'アジア最大級の映画祭。',
                    'start_date' => '2026-04-16',
                    'end_date' => '2026-04-19',
                    'venue' => '那覇市・宜野湾市内各所',
                    'address' => '沖縄県那覇市',
                    'category' => 'exhibition',
                    'image_url' => 'https://placehold.co/400x300/673ab7/white?text=Film+Festival',
                    'url' => null,
                    'price' => 'イベントにより異なる',
                    'source' => 'サンプルデータ',
                ],
            ],
        ];

        // 地域名から該当するイベントを取得
        foreach ($baseEvents as $key => $events) {
            if (str_contains($region, $key) || str_contains($key, $region)) {
                return $events;
            }
        }

        // 汎用イベント（地域が見つからない場合）
        return $this->generateGenericEvents($region);
    }

    /**
     * 汎用イベントを生成
     */
    protected function generateGenericEvents(string $region): array
    {
        $currentYear = date('Y');

        return [
            [
                'id' => 'generic_1',
                'title' => "{$region}地域まつり",
                'description' => "{$region}で開催される地域の伝統的なお祭り。",
                'start_date' => "{$currentYear}-08-15",
                'end_date' => "{$currentYear}-08-16",
                'venue' => "{$region}中央公園",
                'address' => $region,
                'category' => 'festival',
                'image_url' => "https://placehold.co/400x300/4caf50/white?text=" . urlencode($region),
                'url' => null,
                'price' => '入場無料',
                'source' => 'サンプルデータ',
            ],
            [
                'id' => 'generic_2',
                'title' => "{$region}花火大会",
                'description' => "{$region}の夏の風物詩。",
                'start_date' => "{$currentYear}-08-01",
                'end_date' => "{$currentYear}-08-01",
                'venue' => "{$region}河川敷",
                'address' => $region,
                'category' => 'fireworks',
                'image_url' => "https://placehold.co/400x300/ff9800/white?text=Fireworks",
                'url' => null,
                'price' => '観覧無料',
                'source' => 'サンプルデータ',
            ],
        ];
    }

    /**
     * Web検索でイベントを取得
     */
    public function searchEventsFromWeb(string $region, ?string $startDate = null, ?string $endDate = null): array
    {
        $events = [];

        try {
            // 検索クエリを構築
            $dateRange = '';
            if ($startDate) {
                $dateRange = date('Y年m月', strtotime($startDate));
                if ($endDate && $startDate !== $endDate) {
                    $dateRange .= '〜' . date('Y年m月', strtotime($endDate));
                }
            }

            $query = "{$region} イベント {$dateRange}";

            // ここで実際のWeb検索APIを呼び出す
            // 例: Google Custom Search API, Bing Search API など

            Log::info("Event search query: {$query}");

        } catch (\Exception $e) {
            Log::error('Web search error: ' . $e->getMessage());
        }

        return $events;
    }

    /**
     * イベントカテゴリを取得
     */
    public function getCategories(): array
    {
        return self::CATEGORIES;
    }

    /**
     * 都道府県リストを取得
     */
    public function getPrefectures(): array
    {
        return self::PREFECTURES;
    }

    /**
     * 人気のイベント地域を取得
     */
    public function getPopularRegions(): array
    {
        return [
            ['name' => '東京', 'count' => 1250],
            ['name' => '大阪', 'count' => 820],
            ['name' => '京都', 'count' => 680],
            ['name' => '北海道', 'count' => 540],
            ['name' => '沖縄', 'count' => 420],
            ['name' => '福岡', 'count' => 380],
            ['name' => '神奈川', 'count' => 350],
            ['name' => '愛知', 'count' => 320],
        ];
    }

    /**
     * 今月の注目イベントを取得
     */
    public function getFeaturedEvents(): array
    {
        $events = [];
        $popularRegions = ['東京', '大阪', '京都', '北海道'];

        foreach ($popularRegions as $region) {
            $regionEvents = $this->getSampleEventsForRegion($region);
            $events = array_merge($events, array_slice($regionEvents, 0, 1));
        }

        return $events;
    }
}
