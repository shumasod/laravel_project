@extends('layouts.app')

@section('title', "「{$searchParams['region']}」のイベント検索結果")

@push('styles')
<style>
    .search-header {
        background: linear-gradient(135deg, #e91e63 0%, #9c27b0 100%);
        padding: 30px 0;
        color: white;
    }
    .filter-sidebar {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .event-card {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .event-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .event-card img {
        height: 160px;
        object-fit: cover;
    }
    .category-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .event-date {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 6px;
        display: inline-block;
    }
    .favorite-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .favorite-btn:hover {
        background: white;
        transform: scale(1.1);
    }
    .favorite-btn.active {
        color: #e91e63;
    }
    .list-view .event-card {
        flex-direction: row;
    }
    .list-view .event-card img {
        width: 200px;
        height: auto;
        min-height: 150px;
    }
</style>
@endpush

@section('content')
<!-- Search Header -->
<section class="search-header">
    <div class="container">
        <form action="{{ route('events.search') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">地域</label>
                <input type="text" class="form-control" name="region"
                       value="{{ $searchParams['region'] }}" list="region-list" required>
                <datalist id="region-list">
                    @foreach($prefectures as $pref)
                        <option value="{{ $pref }}">
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2">
                <label class="form-label small">開始日</label>
                <input type="date" class="form-control" name="start_date"
                       value="{{ $searchParams['start_date'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">終了日</label>
                <input type="date" class="form-control" name="end_date"
                       value="{{ $searchParams['end_date'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">カテゴリ</label>
                <select name="category" class="form-select">
                    <option value="">すべて</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ ($searchParams['category'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-light w-100">
                    <i class="bi bi-search"></i> 検索
                </button>
            </div>
        </form>
    </div>
</section>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <h5 class="mb-3">絞り込み</h5>

                <!-- カテゴリ -->
                <div class="mb-4">
                    <h6 class="mb-2">カテゴリ</h6>
                    @php
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
                        <a href="{{ route('events.search', array_merge($searchParams, ['category' => $key])) }}"
                           class="d-block text-decoration-none mb-1 {{ ($searchParams['category'] ?? '') === $key ? 'fw-bold' : '' }}">
                            <span class="badge me-1" style="background-color: {{ $categoryColors[$key] ?? '#666' }}">
                                &nbsp;
                            </span>
                            {{ $label }}
                        </a>
                    @endforeach
                    @if($searchParams['category'] ?? null)
                        <a href="{{ route('events.search', array_merge($searchParams, ['category' => null])) }}"
                           class="d-block text-decoration-none mt-2 text-danger small">
                            <i class="bi bi-x"></i> カテゴリをクリア
                        </a>
                    @endif
                </div>

                <!-- 期間クイック選択 -->
                <div class="mb-4">
                    <h6 class="mb-2">期間</h6>
                    <a href="{{ route('events.search', array_merge($searchParams, ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+7 days'))])) }}"
                       class="btn btn-sm btn-outline-secondary w-100 mb-1">今週</a>
                    <a href="{{ route('events.search', array_merge($searchParams, ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+1 month'))])) }}"
                       class="btn btn-sm btn-outline-secondary w-100 mb-1">今月</a>
                    <a href="{{ route('events.search', array_merge($searchParams, ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+3 months'))])) }}"
                       class="btn btn-sm btn-outline-secondary w-100 mb-1">3ヶ月以内</a>
                </div>

                <!-- カレンダー表示リンク -->
                <div>
                    <a href="{{ route('events.calendar', ['region' => $searchParams['region']]) }}"
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-calendar3"></i> カレンダーで見る
                    </a>
                </div>
            </div>
        </div>

        <!-- Event Results -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    「{{ $searchParams['region'] }}」のイベント
                    <span class="badge bg-secondary">{{ count($events) }}件</span>
                </h4>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary active" id="gridViewBtn">
                        <i class="bi bi-grid"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="listViewBtn">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>

            @if(count($events) > 0)
                <div class="row g-4" id="eventContainer">
                    @foreach($events as $event)
                        <div class="col-md-6 col-lg-4 event-item">
                            <div class="event-card card position-relative">
                                <button class="favorite-btn" data-event-id="{{ $event['id'] }}"
                                        onclick="toggleFavorite(this, {{ json_encode($event) }})">
                                    <i class="bi bi-heart"></i>
                                </button>
                                <img src="{{ $event['image_url'] ?? 'https://placehold.co/400x300/e0e0e0/666?text=No+Image' }}"
                                     class="card-img-top" alt="{{ $event['title'] }}">
                                <div class="card-body">
                                    <span class="category-badge text-white mb-2 d-inline-block"
                                          style="background-color: {{ $categoryColors[$event['category']] ?? '#666' }}">
                                        {{ $categories[$event['category']] ?? 'その他' }}
                                    </span>
                                    <h6 class="card-title">{{ $event['title'] }}</h6>

                                    <div class="event-date mb-2">
                                        <i class="bi bi-calendar text-primary me-1"></i>
                                        <strong>{{ date('Y/m/d', strtotime($event['start_date'])) }}</strong>
                                        @if(($event['end_date'] ?? $event['start_date']) !== $event['start_date'])
                                            <span class="text-muted">〜 {{ date('m/d', strtotime($event['end_date'])) }}</span>
                                        @endif
                                    </div>

                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $event['venue'] }}
                                    </p>

                                    @if($event['price'] ?? null)
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-currency-yen me-1"></i>{{ $event['price'] }}
                                        </p>
                                    @endif

                                    <p class="card-text small text-muted mb-3">
                                        {{ \Illuminate\Support\Str::limit($event['description'], 80) }}
                                    </p>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('events.show', $event['id']) }}"
                                           class="btn btn-sm btn-primary flex-grow-1">
                                            詳細を見る
                                        </a>
                                        @if($event['url'] ?? null)
                                            <a href="{{ $event['url'] }}" target="_blank"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h5 class="mt-3">イベントが見つかりませんでした</h5>
                    <p class="text-muted">検索条件を変更してお試しください</p>
                    <a href="{{ route('events.index') }}" class="btn btn-primary">
                        トップに戻る
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 表示切り替え
document.getElementById('gridViewBtn')?.addEventListener('click', function() {
    document.getElementById('eventContainer').classList.remove('list-view');
    this.classList.add('active');
    document.getElementById('listViewBtn').classList.remove('active');
});

document.getElementById('listViewBtn')?.addEventListener('click', function() {
    document.getElementById('eventContainer').classList.add('list-view');
    this.classList.add('active');
    document.getElementById('gridViewBtn').classList.remove('active');
});

// お気に入り切り替え
function toggleFavorite(btn, eventData) {
    const eventId = btn.dataset.eventId;
    const isActive = btn.classList.contains('active');

    if (isActive) {
        // 削除
        fetch(`/events/favorites/${eventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        }).then(() => {
            btn.classList.remove('active');
            btn.querySelector('i').classList.remove('bi-heart-fill');
            btn.querySelector('i').classList.add('bi-heart');
        });
    } else {
        // 追加
        fetch('/events/favorites', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                event_id: eventId,
                event_data: eventData
            })
        }).then(() => {
            btn.classList.add('active');
            btn.querySelector('i').classList.remove('bi-heart');
            btn.querySelector('i').classList.add('bi-heart-fill');
        });
    }
}
</script>
@endpush
