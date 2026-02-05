@extends('layouts.app')

@section('title', 'イベント検索 - 地域のイベント情報')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #e91e63 0%, #9c27b0 100%);
        padding: 60px 0;
        color: white;
    }
    .search-box {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    .search-box .form-label {
        color: #333;
        font-weight: 500;
        font-size: 0.85rem;
    }
    .category-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        text-decoration: none;
    }
    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .event-card {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s;
        height: 100%;
    }
    .event-card:hover {
        transform: translateY(-2px);
    }
    .event-card img {
        height: 180px;
        object-fit: cover;
    }
    .category-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .region-tag {
        display: inline-block;
        padding: 8px 16px;
        background: #f5f5f5;
        border-radius: 20px;
        margin: 4px;
        text-decoration: none;
        color: #333;
        transition: all 0.2s;
    }
    .region-tag:hover {
        background: #e91e63;
        color: white;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="text-center mb-3">地域のイベントを探そう</h1>
        <p class="text-center mb-4 opacity-75">日本全国のお祭り、コンサート、展覧会など様々なイベント情報を検索</p>

        <div class="search-box mx-auto" style="max-width: 900px;">
            <form action="{{ route('events.search') }}" method="GET">
                <div class="row g-3">
                    <!-- 地域 -->
                    <div class="col-md-4">
                        <label class="form-label">地域・都道府県</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" class="form-control" name="region"
                                   placeholder="東京、大阪、京都など" list="region-list" required>
                            <datalist id="region-list">
                                @foreach($prefectures as $pref)
                                    <option value="{{ $pref }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <!-- 開始日 -->
                    <div class="col-md-2">
                        <label class="form-label">開始日</label>
                        <input type="date" class="form-control" name="start_date"
                               value="{{ date('Y-m-d') }}"
                               min="{{ date('Y-m-d') }}">
                    </div>

                    <!-- 終了日 -->
                    <div class="col-md-2">
                        <label class="form-label">終了日</label>
                        <input type="date" class="form-control" name="end_date"
                               value="{{ date('Y-m-d', strtotime('+3 months')) }}">
                    </div>

                    <!-- カテゴリ -->
                    <div class="col-md-2">
                        <label class="form-label">カテゴリ</label>
                        <select name="category" class="form-select">
                            <option value="">すべて</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 検索ボタン -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-danger w-100 py-2">
                            <i class="bi bi-search"></i> 検索
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Popular Regions -->
<section class="py-5">
    <div class="container">
        <h2 class="h4 mb-4">人気のエリア</h2>
        <div class="d-flex flex-wrap justify-content-center">
            @foreach($popularRegions as $region)
                <a href="{{ route('events.search', ['region' => $region['name']]) }}" class="region-tag">
                    <i class="bi bi-geo-alt me-1"></i>{{ $region['name'] }}
                    <span class="badge bg-secondary ms-1">{{ number_format($region['count']) }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Event Categories -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="h4 mb-4">カテゴリから探す</h2>
        <div class="row g-3">
            @php
                $categoryIcons = [
                    'festival' => 'bi-flag',
                    'concert' => 'bi-music-note-beamed',
                    'exhibition' => 'bi-easel',
                    'sports' => 'bi-trophy',
                    'fireworks' => 'bi-stars',
                    'food' => 'bi-cup-straw',
                    'traditional' => 'bi-building',
                    'illumination' => 'bi-lightbulb',
                    'market' => 'bi-shop',
                    'other' => 'bi-three-dots',
                ];
                $categoryColors = [
                    'festival' => '#e91e63',
                    'concert' => '#9c27b0',
                    'exhibition' => '#673ab7',
                    'sports' => '#2196f3',
                    'fireworks' => '#ff9800',
                    'food' => '#4caf50',
                    'traditional' => '#795548',
                    'illumination' => '#ffc107',
                    'market' => '#00bcd4',
                    'other' => '#607d8b',
                ];
            @endphp
            @foreach($categories as $key => $label)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('events.search', ['region' => '東京', 'category' => $key]) }}"
                       class="category-card card h-100 text-decoration-none">
                        <div class="card-body text-center py-4">
                            <i class="bi {{ $categoryIcons[$key] ?? 'bi-calendar-event' }} fs-1"
                               style="color: {{ $categoryColors[$key] ?? '#666' }}"></i>
                            <h6 class="card-title mt-2 mb-0 text-dark">{{ $label }}</h6>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Events -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">注目のイベント</h2>
            <a href="{{ route('events.search', ['region' => '東京']) }}" class="btn btn-outline-primary btn-sm">
                もっと見る <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="row g-4">
            @foreach($featuredEvents as $event)
                <div class="col-md-6 col-lg-3">
                    <div class="event-card card">
                        <img src="{{ $event['image_url'] ?? 'https://placehold.co/400x300/e0e0e0/666?text=No+Image' }}"
                             class="card-img-top" alt="{{ $event['title'] }}">
                        <div class="card-body">
                            <span class="category-badge text-white mb-2 d-inline-block"
                                  style="background-color: {{ $categoryColors[$event['category']] ?? '#666' }}">
                                {{ $categories[$event['category']] ?? 'その他' }}
                            </span>
                            <h6 class="card-title">{{ $event['title'] }}</h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-calendar me-1"></i>
                                {{ date('Y/m/d', strtotime($event['start_date'])) }}
                                @if($event['end_date'] !== $event['start_date'])
                                    〜 {{ date('m/d', strtotime($event['end_date'])) }}
                                @endif
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt me-1"></i>{{ $event['venue'] }}
                            </p>
                            <a href="{{ route('events.show', $event['id']) }}"
                               class="btn btn-sm btn-outline-primary w-100">
                                詳細を見る
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Search by Prefecture -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="h4 mb-4">都道府県から探す</h2>
        <div class="row">
            @php
                $regions = [
                    '北海道・東北' => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県'],
                    '関東' => ['東京都', '神奈川県', '千葉県', '埼玉県', '茨城県', '栃木県', '群馬県'],
                    '中部' => ['新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県'],
                    '関西' => ['三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県'],
                    '中国・四国' => ['鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県'],
                    '九州・沖縄' => ['福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'],
                ];
            @endphp
            @foreach($regions as $regionName => $prefs)
                <div class="col-md-6 col-lg-4 mb-4">
                    <h6 class="text-muted mb-2">{{ $regionName }}</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($prefs as $pref)
                            <a href="{{ route('events.search', ['region' => $pref]) }}"
                               class="btn btn-sm btn-outline-secondary">
                                {{ str_replace(['都', '府', '県'], '', $pref) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
