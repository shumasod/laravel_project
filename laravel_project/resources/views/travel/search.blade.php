@extends('layouts.app')

@section('title', '検索結果 - 旅行検索')

@push('styles')
<style>
    .filter-sidebar {
        position: sticky;
        top: 20px;
    }
    .filter-section {
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 16px;
        margin-bottom: 16px;
    }
    .filter-section:last-child {
        border-bottom: none;
    }
    .accommodation-item {
        border-radius: 8px;
        transition: box-shadow 0.2s;
    }
    .accommodation-item:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .accommodation-item img {
        width: 200px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
    }
    .rating-badge {
        background: #ffc107;
        color: #333;
        font-weight: bold;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .price-display {
        color: #e53935;
        font-size: 1.5rem;
        font-weight: bold;
    }
    .stock-warning {
        color: #f57c00;
        font-size: 0.85rem;
    }
    .photo-thumbnails img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }
    .amenity-badge {
        font-size: 0.75rem;
        padding: 2px 6px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- 検索バー（再検索用） -->
    <div class="bg-white rounded shadow-sm p-3 mb-4">
        <form action="{{ route('travel.search') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control" name="keyword"
                       value="{{ $params['keyword'] ?? '' }}" placeholder="目的地・施設名">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="check_in"
                       value="{{ $params['check_in'] ?? '' }}" min="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="check_out"
                       value="{{ $params['check_out'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="guests" class="form-select">
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ ($params['guests'] ?? 2) == $i ? 'selected' : '' }}>大人{{ $i }}名</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <select name="rooms" class="form-select">
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ ($params['rooms'] ?? 1) == $i ? 'selected' : '' }}>{{ $i }}室</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-danger w-100">検索</button>
            </div>
        </form>
    </div>

    <div class="row">
        <!-- フィルターサイドバー -->
        <div class="col-md-3">
            <div class="filter-sidebar bg-white rounded shadow-sm p-3">
                <h6 class="mb-3">絞り込み</h6>

                <!-- 施設タイプ -->
                <div class="filter-section">
                    <label class="form-label small fw-bold">施設タイプ</label>
                    @foreach($results['filters']['facility_types'] as $type)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input filter-checkbox"
                                   name="facility_type[]" value="{{ $type['value'] }}"
                                   id="type-{{ $type['value'] }}"
                                   {{ in_array($type['value'], $params['facility_type'] ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="type-{{ $type['value'] }}">
                                {{ $type['label'] }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <!-- 食事条件 -->
                <div class="filter-section">
                    <label class="form-label small fw-bold">食事</label>
                    @foreach($results['filters']['meal_types'] as $meal)
                        <div class="form-check">
                            <input type="radio" class="form-check-input filter-radio"
                                   name="meal_type" value="{{ $meal['value'] }}"
                                   id="meal-{{ $meal['value'] }}"
                                   {{ ($params['meal_type'] ?? '') == $meal['value'] ? 'checked' : '' }}>
                            <label class="form-check-label small" for="meal-{{ $meal['value'] }}">
                                {{ $meal['label'] }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <!-- 価格帯 -->
                <div class="filter-section">
                    <label class="form-label small fw-bold">価格帯（1人1泊）</label>
                    @foreach($results['filters']['price_ranges'] as $range)
                        <div class="form-check">
                            <input type="radio" class="form-check-input filter-radio"
                                   name="price_range"
                                   value="{{ $range['min'] }}-{{ $range['max'] ?? '' }}"
                                   id="price-{{ $range['min'] }}">
                            <label class="form-check-label small" for="price-{{ $range['min'] }}">
                                {{ $range['label'] }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <!-- 評価 -->
                <div class="filter-section">
                    <label class="form-label small fw-bold">口コミ評価</label>
                    @foreach($results['filters']['review_scores'] as $score)
                        <div class="form-check">
                            <input type="radio" class="form-check-input filter-radio"
                                   name="min_review_score" value="{{ $score }}"
                                   id="score-{{ $score }}"
                                   {{ ($params['min_review_score'] ?? '') == $score ? 'checked' : '' }}>
                            <label class="form-check-label small" for="score-{{ $score }}">
                                {{ $score }}以上
                            </label>
                        </div>
                    @endforeach
                </div>

                <!-- 星評価 -->
                <div class="filter-section">
                    <label class="form-label small fw-bold">星評価</label>
                    @foreach($results['filters']['star_ratings'] as $star)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input filter-checkbox"
                                   name="star_rating[]" value="{{ $star }}"
                                   id="star-{{ $star }}"
                                   {{ in_array($star, $params['star_rating'] ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="star-{{ $star }}">
                                @for($i = 0; $i < $star; $i++) ★ @endfor
                            </label>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="apply-filters">
                    絞り込みを適用
                </button>
            </div>
        </div>

        <!-- 検索結果 -->
        <div class="col-md-9">
            <!-- 結果ヘッダー -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="fw-bold">{{ number_format($results['pagination']['total']) }}件</span>の宿泊施設が見つかりました
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted">並び替え:</label>
                    <select class="form-select form-select-sm" style="width: auto;" id="sort-select">
                        <option value="recommended" {{ ($params['sort'] ?? '') == 'recommended' ? 'selected' : '' }}>おすすめ順</option>
                        <option value="price_asc" {{ ($params['sort'] ?? '') == 'price_asc' ? 'selected' : '' }}>料金の安い順</option>
                        <option value="price_desc" {{ ($params['sort'] ?? '') == 'price_desc' ? 'selected' : '' }}>料金の高い順</option>
                        <option value="rating_desc" {{ ($params['sort'] ?? '') == 'rating_desc' ? 'selected' : '' }}>評価の高い順</option>
                        <option value="review_count_desc" {{ ($params['sort'] ?? '') == 'review_count_desc' ? 'selected' : '' }}>口コミ件数順</option>
                    </select>
                </div>
            </div>

            <!-- 施設リスト -->
            <div class="accommodation-list">
                @forelse($results['items'] as $item)
                    <div class="accommodation-item card mb-3">
                        <div class="row g-0">
                            <div class="col-md-3 p-3">
                                <a href="{{ route('travel.show', ['id' => $item['id']]) }}">
                                    <img src="{{ $item['main_photo'] ?? 'https://placehold.co/400x300/e0e0e0/666?text=No+Image' }}"
                                         class="img-fluid rounded" alt="{{ $item['name'] }}">
                                </a>
                                @if(count($item['photos'] ?? []) > 1)
                                    <div class="photo-thumbnails mt-2 d-flex gap-1">
                                        @foreach(array_slice($item['photos']->toArray(), 1, 4) as $photo)
                                            <img src="{{ $photo }}" alt="">
                                        @endforeach
                                        @if(count($item['photos'] ?? []) > 5)
                                            <span class="badge bg-secondary align-self-center">+{{ count($item['photos']) - 5 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <a href="{{ route('travel.show', ['id' => $item['id']]) }}" class="text-decoration-none text-dark">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">
                                                {{ $item['area_name'] }}
                                                @if($item['access'])
                                                    / {{ $item['access'] }}
                                                @endif
                                            </p>
                                        </div>
                                        @if($item['review_score'])
                                            <div class="text-end">
                                                <span class="rating-badge">{{ number_format($item['review_score'], 1) }}</span>
                                                <br>
                                                <small class="text-muted">{{ $item['review_count'] }}件</small>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- アメニティバッジ -->
                                    @if(!empty($item['highlight_features']))
                                        <div class="mb-2">
                                            @foreach(array_slice($item['highlight_features'], 0, 5) as $feature)
                                                <span class="badge bg-light text-secondary amenity-badge">{{ $feature }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- 星評価 -->
                                    @if($item['star_rating'])
                                        <div class="mb-2">
                                            @for($i = 0; $i < $item['star_rating']; $i++)
                                                <i class="bi bi-star-fill text-warning"></i>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 d-flex flex-column justify-content-center align-items-end p-3 border-start">
                                @if($item['min_price'])
                                    <div class="text-end mb-3">
                                        <span class="price-display">¥{{ number_format($item['min_price']) }}</span>
                                        <span class="text-muted small">〜/1人</span>
                                    </div>
                                @endif
                                <a href="{{ route('travel.show', ['id' => $item['id'], 'check_in' => $params['check_in'] ?? '', 'check_out' => $params['check_out'] ?? '', 'guests' => $params['guests'] ?? 2]) }}"
                                   class="btn btn-danger">
                                    空室を確認
                                </a>
                                <button class="btn btn-link btn-sm text-muted mt-2 favorite-btn"
                                        data-id="{{ $item['id'] }}">
                                    <i class="bi bi-heart"></i> お気に入り
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">
                        条件に一致する宿泊施設が見つかりませんでした。<br>
                        検索条件を変更してお試しください。
                    </div>
                @endforelse
            </div>

            <!-- ページネーション -->
            @if($results['pagination']['last_page'] > 1)
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        @for($i = 1; $i <= $results['pagination']['last_page']; $i++)
                            <li class="page-item {{ $results['pagination']['current_page'] == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ソート変更
document.getElementById('sort-select')?.addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', this.value);
    window.location.href = url.toString();
});

// フィルター適用
document.getElementById('apply-filters')?.addEventListener('click', function() {
    const form = document.querySelector('form');
    const url = new URL(window.location.href);

    // チェックボックス
    document.querySelectorAll('.filter-checkbox:checked').forEach(cb => {
        url.searchParams.append(cb.name, cb.value);
    });

    // ラジオボタン
    document.querySelectorAll('.filter-radio:checked').forEach(rb => {
        url.searchParams.set(rb.name, rb.value);
    });

    window.location.href = url.toString();
});

// お気に入り
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch('{{ route("travel.favorites.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ accommodation_id: id })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                this.innerHTML = '<i class="bi bi-heart-fill text-danger"></i> お気に入り済み';
            }
        });
    });
});
</script>
@endpush
