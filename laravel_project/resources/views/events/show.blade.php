@extends('layouts.app')

@section('title', $event['title'] . ' - イベント詳細')

@push('styles')
<style>
    .event-hero {
        position: relative;
        height: 350px;
        overflow: hidden;
    }
    .event-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .event-hero-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        padding: 40px 20px 20px;
        color: white;
    }
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .info-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 16px;
    }
    .info-item i {
        width: 24px;
        color: #e91e63;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .category-badge {
        font-size: 0.8rem;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .related-event-card {
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .related-event-card:hover {
        transform: translateY(-2px);
    }
    .related-event-card img {
        height: 120px;
        object-fit: cover;
    }
    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
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

<!-- Hero Image -->
<div class="event-hero">
    <img src="{{ $event['image_url'] ?? 'https://placehold.co/1200x400/e0e0e0/666?text=No+Image' }}"
         alt="{{ $event['title'] }}">
    <div class="event-hero-overlay">
        <div class="container">
            <span class="category-badge text-white mb-2 d-inline-block"
                  style="background-color: {{ $categoryColors[$event['category']] ?? '#666' }}">
                {{ $categories[$event['category']] ?? 'その他' }}
            </span>
            <h1 class="h2 mb-0">{{ $event['title'] }}</h1>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Event Info Card -->
            <div class="info-card mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="bi bi-calendar-event fs-5"></i>
                            <div>
                                <strong>開催日</strong><br>
                                <span>{{ date('Y年m月d日', strtotime($event['start_date'])) }}</span>
                                @if(($event['end_date'] ?? $event['start_date']) !== $event['start_date'])
                                    <span>〜 {{ date('Y年m月d日', strtotime($event['end_date'])) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="bi bi-geo-alt fs-5"></i>
                            <div>
                                <strong>会場</strong><br>
                                <span>{{ $event['venue'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="bi bi-pin-map fs-5"></i>
                            <div>
                                <strong>住所</strong><br>
                                <span>{{ $event['address'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <i class="bi bi-currency-yen fs-5"></i>
                            <div>
                                <strong>料金</strong><br>
                                <span>{{ $event['price'] ?? '情報なし' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="info-card mb-4">
                <h5 class="mb-3">イベント概要</h5>
                <p class="mb-0">{{ $event['description'] }}</p>
            </div>

            <!-- Map Placeholder -->
            <div class="info-card mb-4">
                <h5 class="mb-3">会場アクセス</h5>
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-map display-4"></i>
                        <p class="mt-2">{{ $event['address'] }}</p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event['address']) }}"
                           target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-arrow-up-right"></i> Google Mapsで開く
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Events -->
            @if(count($relatedEvents) > 0)
                <div class="info-card">
                    <h5 class="mb-3">関連イベント</h5>
                    <div class="row g-3">
                        @foreach($relatedEvents as $relEvent)
                            <div class="col-md-6">
                                <a href="{{ route('events.show', $relEvent['id']) }}"
                                   class="text-decoration-none">
                                    <div class="related-event-card card">
                                        <img src="{{ $relEvent['image_url'] ?? 'https://placehold.co/300x200/e0e0e0/666?text=No+Image' }}"
                                             class="card-img-top" alt="{{ $relEvent['title'] }}">
                                        <div class="card-body py-2">
                                            <h6 class="card-title mb-1 text-dark">{{ $relEvent['title'] }}</h6>
                                            <small class="text-muted">
                                                {{ date('Y/m/d', strtotime($relEvent['start_date'])) }}
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Buttons -->
            <div class="info-card mb-4">
                @if($event['url'] ?? null)
                    <a href="{{ $event['url'] }}" target="_blank"
                       class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> 公式サイトへ
                    </a>
                @endif

                <button class="btn btn-outline-danger w-100 mb-3" id="favoriteBtn"
                        onclick="toggleFavorite()">
                    <i class="bi bi-heart me-1"></i> お気に入りに追加
                </button>

                <!-- Share Buttons -->
                <div class="d-flex justify-content-center gap-2">
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($event['title']) }}&url={{ urlencode(request()->url()) }}"
                       target="_blank" class="share-btn btn btn-outline-secondary">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                       target="_blank" class="share-btn btn btn-outline-secondary">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://line.me/R/msg/text/?{{ urlencode($event['title'] . ' ' . request()->url()) }}"
                       target="_blank" class="share-btn btn btn-outline-secondary">
                        <i class="bi bi-line"></i>
                    </a>
                    <button class="share-btn btn btn-outline-secondary" onclick="copyUrl()">
                        <i class="bi bi-link-45deg"></i>
                    </button>
                </div>
            </div>

            <!-- Quick Info -->
            <div class="info-card mb-4">
                <h6 class="mb-3">基本情報</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">カテゴリ</td>
                        <td>{{ $categories[$event['category']] ?? 'その他' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">開催期間</td>
                        <td>
                            {{ date('m/d', strtotime($event['start_date'])) }}
                            @if(($event['end_date'] ?? $event['start_date']) !== $event['start_date'])
                                〜 {{ date('m/d', strtotime($event['end_date'])) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">会場</td>
                        <td>{{ $event['venue'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">料金</td>
                        <td>{{ $event['price'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">情報元</td>
                        <td>{{ $event['source'] ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Search nearby accommodations -->
            <div class="info-card">
                <h6 class="mb-3">周辺の宿を探す</h6>
                <p class="small text-muted mb-3">
                    このイベントに便利な宿泊施設を検索
                </p>
                <a href="{{ route('travel.search', ['keyword' => $event['address'], 'check_in' => $event['start_date']]) }}"
                   class="btn btn-outline-primary w-100">
                    <i class="bi bi-building me-1"></i> 周辺の宿を検索
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="container pb-4">
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> 検索結果に戻る
    </a>
</div>
@endsection

@push('scripts')
<script>
function toggleFavorite() {
    const btn = document.getElementById('favoriteBtn');
    const eventId = '{{ $event["id"] }}';
    const eventData = @json($event);
    const isActive = btn.classList.contains('active');

    if (isActive) {
        fetch(`/events/favorites/${eventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        }).then(() => {
            btn.classList.remove('active', 'btn-danger');
            btn.classList.add('btn-outline-danger');
            btn.innerHTML = '<i class="bi bi-heart me-1"></i> お気に入りに追加';
        });
    } else {
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
            btn.classList.add('active', 'btn-danger');
            btn.classList.remove('btn-outline-danger');
            btn.innerHTML = '<i class="bi bi-heart-fill me-1"></i> お気に入り登録済み';
        });
    }
}

function copyUrl() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('URLをコピーしました');
    });
}
</script>
@endpush
