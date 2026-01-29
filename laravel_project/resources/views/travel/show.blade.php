@extends('layouts.app')

@section('title', $accommodation['name'] . ' - 旅行検索')

@push('styles')
<style>
    .gallery-main img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 8px;
    }
    .gallery-thumb {
        width: 100%;
        height: 95px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .gallery-thumb:hover, .gallery-thumb.active {
        opacity: 1;
    }
    .rating-large {
        background: #ffc107;
        color: #333;
        font-weight: bold;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 1.5rem;
    }
    .rating-bar {
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
    }
    .rating-bar-fill {
        height: 100%;
        background: #ffc107;
    }
    .plan-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: border-color 0.2s;
    }
    .plan-card:hover {
        border-color: #667eea;
    }
    .plan-card.selected {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    }
    .price-highlight {
        color: #e53935;
        font-size: 1.75rem;
        font-weight: bold;
    }
    .sticky-booking {
        position: sticky;
        bottom: 0;
        background: white;
        border-top: 1px solid #e0e0e0;
        padding: 16px;
        z-index: 100;
    }
    .nav-tabs .nav-link {
        color: #666;
    }
    .nav-tabs .nav-link.active {
        color: #667eea;
        font-weight: 500;
    }
    .amenity-group h6 {
        font-size: 0.9rem;
        color: #666;
    }
    .review-card {
        border-left: 3px solid #ffc107;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- パンくず -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('travel.index') }}">トップ</a></li>
            @if($accommodation['area'])
                <li class="breadcrumb-item"><a href="{{ route('travel.search', ['area_id' => $accommodation['area']['id']]) }}">{{ $accommodation['area']['name'] }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $accommodation['name'] }}</li>
        </ol>
    </nav>

    <!-- ヘッダー -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-2">{{ $accommodation['name'] }}</h1>
            <p class="text-muted mb-0">
                @if($accommodation['ratings']['star'])
                    @for($i = 0; $i < $accommodation['ratings']['star']; $i++)
                        <i class="bi bi-star-fill text-warning"></i>
                    @endfor
                    <span class="mx-2">|</span>
                @endif
                {{ $accommodation['address'] }}
                @if($accommodation['access']['station'])
                    <span class="mx-2">|</span>
                    {{ $accommodation['access']['station'] }}駅から徒歩{{ $accommodation['access']['station_distance'] }}分
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" id="favorite-btn" data-id="{{ $accommodation['id'] }}">
                <i class="bi bi-heart{{ $isFavorite ? '-fill text-danger' : '' }}"></i>
                {{ $isFavorite ? 'お気に入り済み' : 'お気に入り' }}
            </button>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-share"></i> 共有
            </button>
        </div>
    </div>

    <!-- フォトギャラリー -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="gallery-main">
                <img src="{{ $accommodation['photos'][0]['url'] ?? 'https://placehold.co/800x400/e0e0e0/666?text=No+Image' }}"
                     id="main-image" alt="{{ $accommodation['name'] }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="row g-2">
                @foreach(array_slice($accommodation['photos']->toArray(), 0, 4) as $index => $photo)
                    <div class="col-6">
                        <img src="{{ $photo['thumbnail'] ?? $photo['url'] }}"
                             class="gallery-thumb {{ $index === 0 ? 'active' : '' }}"
                             data-url="{{ $photo['url'] }}"
                             alt="{{ $photo['caption'] ?? '' }}">
                    </div>
                @endforeach
            </div>
            @if(count($accommodation['photos']) > 4)
                <button class="btn btn-outline-secondary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#galleryModal">
                    すべての写真を見る ({{ count($accommodation['photos']) }}枚)
                </button>
            @endif
        </div>
    </div>

    <!-- タブナビゲーション -->
    <ul class="nav nav-tabs mb-4" id="detailTabs">
        <li class="nav-item">
            <a class="nav-link active" href="#overview" data-bs-toggle="tab">概要</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#plans" data-bs-toggle="tab">客室・プラン</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#facilities" data-bs-toggle="tab">設備・サービス</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#reviews" data-bs-toggle="tab">口コミ ({{ $accommodation['ratings']['review_count'] }})</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#access" data-bs-toggle="tab">アクセス</a>
        </li>
    </ul>

    <div class="row">
        <div class="col-md-8">
            <div class="tab-content">
                <!-- 概要タブ -->
                <div class="tab-pane fade show active" id="overview">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">施設紹介</h5>
                            <p>{!! nl2br(e($accommodation['description_long'] ?? $accommodation['description'])) !!}</p>

                            @if(!empty($accommodation['highlight_features']))
                                <h6 class="mt-4">ハイライト</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($accommodation['highlight_features'] as $feature)
                                        <span class="badge bg-primary">{{ $feature }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 評価サマリー -->
                    @if($accommodation['ratings']['review_score'])
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center">
                                        <span class="rating-large">{{ number_format($accommodation['ratings']['review_score'], 1) }}</span>
                                        <p class="mb-0 mt-2 small text-muted">{{ $accommodation['ratings']['review_count'] }}件の口コミ</p>
                                    </div>
                                    <div class="col-md-9">
                                        @foreach(['cleanliness' => '清潔さ', 'service' => 'サービス', 'location' => '立地', 'facility' => '設備', 'value' => 'コスパ'] as $key => $label)
                                            @if($accommodation['ratings'][$key])
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-3 small">{{ $label }}</div>
                                                    <div class="col-7">
                                                        <div class="rating-bar">
                                                            <div class="rating-bar-fill" style="width: {{ $accommodation['ratings'][$key] / 5 * 100 }}%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-2 small text-end">{{ number_format($accommodation['ratings'][$key], 1) }}</div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- プランタブ -->
                <div class="tab-pane fade" id="plans">
                    <!-- 検索フォーム -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('travel.show', $accommodation['id']) }}" method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small">チェックイン</label>
                                    <input type="date" class="form-control" name="check_in"
                                           value="{{ request('check_in', date('Y-m-d', strtotime('+1 day'))) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">チェックアウト</label>
                                    <input type="date" class="form-control" name="check_out"
                                           value="{{ request('check_out', date('Y-m-d', strtotime('+2 days'))) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">人数</label>
                                    <select class="form-select" name="guests">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ request('guests', 2) == $i ? 'selected' : '' }}>{{ $i }}名</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">部屋数</label>
                                    <select class="form-select" name="rooms">
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ request('rooms', 1) == $i ? 'selected' : '' }}>{{ $i }}室</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">検索</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- プラン一覧 -->
                    @if(!empty($plans))
                        @foreach($plans as $plan)
                            <div class="plan-card p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="{{ $plan['room']['image'] ?? 'https://placehold.co/200x150/e0e0e0/666?text=Room' }}"
                                             class="img-fluid rounded" alt="{{ $plan['room']['name'] }}">
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1">{{ $plan['room']['name'] }}</h6>
                                        <p class="small text-muted mb-2">定員: {{ $plan['room']['capacity'] }}名</p>
                                        <h6 class="mb-1">{{ $plan['name'] }}</h6>
                                        <p class="small mb-2">
                                            <span class="badge bg-info">{{ ['room_only' => '素泊まり', 'breakfast_only' => '朝食付き', 'half_board' => '1泊2食付き'][$plan['meal_type']] ?? $plan['meal_type'] }}</span>
                                            @if($plan['cancellation_policy'])
                                                <span class="badge bg-success">{{ $plan['cancellation_policy'] }}</span>
                                            @endif
                                        </p>
                                        @if($plan['benefits'])
                                            <ul class="small mb-0">
                                                @foreach($plan['benefits'] as $benefit)
                                                    <li>{{ $benefit }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="col-md-3 text-end d-flex flex-column justify-content-center">
                                        <div class="mb-2">
                                            <span class="price-highlight">¥{{ number_format($plan['price_per_person']) }}</span>
                                            <span class="small text-muted">/1人</span>
                                        </div>
                                        <p class="small text-muted mb-2">総額 ¥{{ number_format($plan['total_price']) }}</p>
                                        <a href="{{ route('travel.booking', ['plan' => $plan['id'], 'check_in' => request('check_in'), 'check_out' => request('check_out'), 'guests' => request('guests', 2), 'rooms' => request('rooms', 1)]) }}"
                                           class="btn btn-danger">このプランを予約</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info">
                            日付と人数を選択して「検索」を押してください。
                        </div>
                    @endif
                </div>

                <!-- 設備タブ -->
                <div class="tab-pane fade" id="facilities">
                    <div class="card">
                        <div class="card-body">
                            @foreach($accommodation['amenities'] as $category => $amenities)
                                <div class="amenity-group mb-4">
                                    <h6>{{ $category }}</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($amenities as $amenity)
                                            <span class="badge bg-light text-dark">{{ $amenity }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            @if($accommodation['parking'])
                                <div class="amenity-group mb-4">
                                    <h6>駐車場</h6>
                                    <p class="mb-0">
                                        {{ $accommodation['parking']['available'] ? 'あり' : 'なし' }}
                                        @if($accommodation['parking']['free'] ?? false) （無料） @endif
                                        @if($accommodation['parking']['capacity'] ?? 0) / {{ $accommodation['parking']['capacity'] }}台 @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 口コミタブ -->
                <div class="tab-pane fade" id="reviews">
                    @foreach($reviews as $review)
                        <div class="review-card card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span class="badge bg-warning text-dark me-2">★{{ $review->overall_rating }}</span>
                                        <strong>{{ $review->title }}</strong>
                                    </div>
                                    <small class="text-muted">{{ $review->created_at->format('Y年m月') }}宿泊</small>
                                </div>
                                <p class="mb-2">{{ $review->comment }}</p>
                                <small class="text-muted">
                                    {{ $review->customer?->name ?? '匿名' }} /
                                    {{ ['business' => 'ビジネス', 'solo' => '一人旅', 'couple' => 'カップル', 'family' => '家族', 'friends' => '友人'][$review->travel_type] ?? '' }}
                                </small>
                            </div>
                        </div>
                    @endforeach

                    @if($reviews->count() > 0)
                        <a href="{{ route('travel.reviews', $accommodation['id']) }}" class="btn btn-outline-primary">
                            すべての口コミを見る
                        </a>
                    @else
                        <p class="text-muted">まだ口コミはありません。</p>
                    @endif
                </div>

                <!-- アクセスタブ -->
                <div class="tab-pane fade" id="access">
                    <div class="card">
                        <div class="card-body">
                            @if($accommodation['latitude'] && $accommodation['longitude'])
                                <div class="mb-4" style="height: 300px; background: #e0e0e0; border-radius: 8px;">
                                    <iframe
                                        width="100%"
                                        height="300"
                                        style="border:0; border-radius: 8px;"
                                        loading="lazy"
                                        src="https://www.google.com/maps?q={{ $accommodation['latitude'] }},{{ $accommodation['longitude'] }}&output=embed">
                                    </iframe>
                                </div>
                            @endif

                            <h6>住所</h6>
                            <p>{{ $accommodation['address'] }}</p>

                            @if($accommodation['access']['info'])
                                <h6>アクセス方法</h6>
                                <ul>
                                    @foreach($accommodation['access']['info'] as $info)
                                        <li>{{ $info['description'] ?? $info }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <h6>連絡先</h6>
                            <p>
                                @if($accommodation['phone']) TEL: {{ $accommodation['phone'] }}<br> @endif
                                @if($accommodation['email']) Email: {{ $accommodation['email'] }} @endif
                            </p>

                            <h6>チェックイン・チェックアウト</h6>
                            <p>
                                チェックイン: {{ $accommodation['check_in_time'] }}<br>
                                チェックアウト: {{ $accommodation['check_out_time'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- サイドバー -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h6>宿泊予約</h6>
                    <form action="{{ route('travel.show', $accommodation['id']) }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label small">チェックイン</label>
                            <input type="date" class="form-control" name="check_in"
                                   value="{{ request('check_in', date('Y-m-d', strtotime('+1 day'))) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">チェックアウト</label>
                            <input type="date" class="form-control" name="check_out"
                                   value="{{ request('check_out', date('Y-m-d', strtotime('+2 days'))) }}">
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small">人数</label>
                                <select class="form-select" name="guests">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ request('guests', 2) == $i ? 'selected' : '' }}>{{ $i }}名</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">部屋</label>
                                <select class="form-select" name="rooms">
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ request('rooms', 1) == $i ? 'selected' : '' }}>{{ $i }}室</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">空室を検索</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ギャラリーサムネイル切り替え
document.querySelectorAll('.gallery-thumb').forEach(thumb => {
    thumb.addEventListener('click', function() {
        document.getElementById('main-image').src = this.dataset.url;
        document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});

// お気に入りボタン
document.getElementById('favorite-btn')?.addEventListener('click', function() {
    const id = this.dataset.id;
    const isFavorite = this.querySelector('i').classList.contains('bi-heart-fill');

    if (isFavorite) {
        fetch(`{{ url('travel/favorites') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => {
            this.innerHTML = '<i class="bi bi-heart"></i> お気に入り';
        });
    } else {
        fetch('{{ route("travel.favorites.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ accommodation_id: id })
        }).then(() => {
            this.innerHTML = '<i class="bi bi-heart-fill text-danger"></i> お気に入り済み';
        });
    }
});
</script>
@endpush
