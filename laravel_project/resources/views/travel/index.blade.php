@extends('layouts.app')

@section('title', '旅行検索 - 宿泊予約')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    .area-card {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .area-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .area-card img {
        height: 120px;
        object-fit: cover;
    }
    .accommodation-card {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .accommodation-card:hover {
        transform: translateY(-2px);
    }
    .accommodation-card img {
        height: 180px;
        object-fit: cover;
    }
    .rating-badge {
        background: #ffc107;
        color: #333;
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    .price-tag {
        color: #e53935;
        font-size: 1.25rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<!-- Hero Section with Search -->
<section class="hero-section">
    <div class="container">
        <h1 class="text-center mb-4">理想の宿を見つけよう</h1>
        <p class="text-center mb-4 opacity-75">国内40,000件以上の宿泊施設から検索</p>

        <div class="search-box mx-auto" style="max-width: 900px;">
            <form action="{{ route('travel.search') }}" method="GET">
                <div class="row g-3">
                    <!-- 目的地 -->
                    <div class="col-md-4">
                        <label class="form-label">目的地・施設名</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" class="form-control" name="keyword"
                                   placeholder="エリア、施設名、駅名" id="keyword-input"
                                   autocomplete="off">
                        </div>
                        <div id="suggest-dropdown" class="dropdown-menu w-100" style="display: none;"></div>
                    </div>

                    <!-- チェックイン -->
                    <div class="col-md-2">
                        <label class="form-label">チェックイン</label>
                        <input type="date" class="form-control" name="check_in"
                               value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               min="{{ date('Y-m-d') }}">
                    </div>

                    <!-- チェックアウト -->
                    <div class="col-md-2">
                        <label class="form-label">チェックアウト</label>
                        <input type="date" class="form-control" name="check_out"
                               value="{{ date('Y-m-d', strtotime('+2 days')) }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>

                    <!-- 人数 -->
                    <div class="col-md-2">
                        <label class="form-label">人数・部屋</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary w-100 text-start dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" id="guest-display">
                                大人2名 1室
                            </button>
                            <div class="dropdown-menu p-3" style="min-width: 250px;">
                                <div class="mb-3">
                                    <label class="form-label small">大人</label>
                                    <select name="guests" class="form-select form-select-sm" id="guests-select">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ $i == 2 ? 'selected' : '' }}>{{ $i }}名</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small">部屋数</label>
                                    <select name="rooms" class="form-select form-select-sm" id="rooms-select">
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}室</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
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

<!-- Popular Areas -->
<section class="py-5">
    <div class="container">
        <h2 class="h4 mb-4">人気のエリア</h2>
        <div class="row g-3">
            @foreach($popularAreas as $area)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('travel.search', ['area_id' => $area['id']]) }}" class="text-decoration-none">
                        <div class="area-card card h-100">
                            <img src="https://placehold.co/300x200/667eea/white?text={{ urlencode($area['name']) }}"
                                 class="card-img-top" alt="{{ $area['name'] }}">
                            <div class="card-body py-2 text-center">
                                <h6 class="card-title mb-0 text-dark">{{ $area['name'] }}</h6>
                                <small class="text-muted">{{ number_format($area['accommodation_count']) }}件</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Accommodations -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="h4 mb-4">おすすめの宿</h2>
        <div class="row g-4">
            @foreach($featuredAccommodations as $accommodation)
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('travel.show', $accommodation['id']) }}" class="text-decoration-none">
                        <div class="accommodation-card card h-100">
                            <img src="{{ $accommodation['main_photo'] ?? 'https://placehold.co/400x300/e0e0e0/666?text=No+Image' }}"
                                 class="card-img-top" alt="{{ $accommodation['name'] }}">
                            <div class="card-body">
                                <h6 class="card-title text-dark mb-1">{{ $accommodation['name'] }}</h6>
                                <p class="text-muted small mb-2">
                                    {{ $accommodation['area_name'] }}
                                    @if($accommodation['access'])
                                        / {{ $accommodation['access'] }}
                                    @endif
                                </p>
                                <div class="d-flex align-items-center mb-2">
                                    @if($accommodation['review_score'])
                                        <span class="rating-badge me-2">{{ $accommodation['review_score'] }}</span>
                                        <small class="text-muted">({{ $accommodation['review_count'] }}件)</small>
                                    @endif
                                </div>
                                @if($accommodation['highlight_features'])
                                    <div class="mb-2">
                                        @foreach(array_slice($accommodation['highlight_features'], 0, 3) as $feature)
                                            <span class="badge bg-light text-secondary me-1">{{ $feature }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="text-end">
                                    @if($accommodation['min_price'])
                                        <span class="price-tag">¥{{ number_format($accommodation['min_price']) }}〜</span>
                                        <small class="text-muted">/1人</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Search by Prefecture -->
<section class="py-5">
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
            @foreach($regions as $region => $prefs)
                <div class="col-md-6 col-lg-4 mb-4">
                    <h6 class="text-muted mb-2">{{ $region }}</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($prefs as $pref)
                            <a href="{{ route('travel.search', ['prefecture' => $pref]) }}"
                               class="btn btn-sm btn-outline-secondary">{{ str_replace(['都', '府', '県'], '', $pref) }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// サジェスト機能
const keywordInput = document.getElementById('keyword-input');
const suggestDropdown = document.getElementById('suggest-dropdown');
let debounceTimer;

keywordInput?.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const keyword = this.value.trim();

    if (keyword.length < 1) {
        suggestDropdown.style.display = 'none';
        return;
    }

    debounceTimer = setTimeout(() => {
        fetch(`{{ route('travel.suggest') }}?q=${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(data => {
                if (data.data.length === 0) {
                    suggestDropdown.style.display = 'none';
                    return;
                }

                suggestDropdown.innerHTML = data.data.map(item => `
                    <a class="dropdown-item" href="#" data-type="${item.type}" data-id="${item.id}" data-name="${item.name}">
                        <small class="text-muted">${item.type === 'area' ? 'エリア' : '施設'}</small>
                        ${item.label}
                    </a>
                `).join('');
                suggestDropdown.style.display = 'block';
            });
    }, 300);
});

suggestDropdown?.addEventListener('click', function(e) {
    const item = e.target.closest('.dropdown-item');
    if (item) {
        e.preventDefault();
        keywordInput.value = item.dataset.name;
        suggestDropdown.style.display = 'none';
    }
});

// 人数・部屋表示更新
document.getElementById('guests-select')?.addEventListener('change', updateGuestDisplay);
document.getElementById('rooms-select')?.addEventListener('change', updateGuestDisplay);

function updateGuestDisplay() {
    const guests = document.getElementById('guests-select').value;
    const rooms = document.getElementById('rooms-select').value;
    document.getElementById('guest-display').textContent = `大人${guests}名 ${rooms}室`;
}
</script>
@endpush
