@extends('layouts.app')

@section('title', "イベントカレンダー - {$region}")

@push('styles')
<style>
    .calendar-header {
        background: linear-gradient(135deg, #e91e63 0%, #9c27b0 100%);
        padding: 20px 0;
        color: white;
    }
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .calendar-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .calendar-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: center;
        font-weight: 600;
    }
    .calendar-table td {
        border: 1px solid #e0e0e0;
        padding: 8px;
        vertical-align: top;
        height: 120px;
        width: 14.28%;
    }
    .calendar-day {
        font-weight: 600;
        margin-bottom: 4px;
    }
    .calendar-day.today {
        color: #e91e63;
    }
    .calendar-day.other-month {
        color: #ccc;
    }
    .calendar-event {
        font-size: 0.75rem;
        padding: 2px 4px;
        margin-bottom: 2px;
        border-radius: 3px;
        color: white;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }
    .calendar-event:hover {
        opacity: 0.8;
    }
    .event-list-item {
        border-left: 4px solid #e91e63;
        padding: 12px;
        margin-bottom: 12px;
        background: white;
        border-radius: 0 8px 8px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')
<!-- Header -->
<section class="calendar-header">
    <div class="container">
        <form action="{{ route('events.calendar') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">地域</label>
                <input type="text" class="form-control" name="region" value="{{ $region }}" list="region-list">
                <datalist id="region-list">
                    @foreach($prefectures as $pref)
                        <option value="{{ $pref }}">
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-3">
                <label class="form-label small">月</label>
                <input type="month" class="form-control" name="month" value="{{ $month }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-light w-100">
                    <i class="bi bi-search"></i> 表示
                </button>
            </div>
        </form>
    </div>
</section>

<div class="container py-4">
    <!-- Calendar Navigation -->
    @php
        $currentMonth = \Carbon\Carbon::parse($month . '-01');
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
    @endphp

    <div class="calendar-nav">
        <a href="{{ route('events.calendar', ['region' => $region, 'month' => $prevMonth]) }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> 前月
        </a>
        <h3 class="mb-0">{{ $currentMonth->format('Y年m月') }}</h3>
        <a href="{{ route('events.calendar', ['region' => $region, 'month' => $nextMonth]) }}"
           class="btn btn-outline-secondary">
            次月 <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <!-- Calendar Table -->
            <table class="calendar-table">
                <thead>
                    <tr>
                        <th class="text-danger">日</th>
                        <th>月</th>
                        <th>火</th>
                        <th>水</th>
                        <th>木</th>
                        <th>金</th>
                        <th class="text-primary">土</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $startOfMonth = $currentMonth->copy()->startOfMonth();
                        $endOfMonth = $currentMonth->copy()->endOfMonth();
                        $startOfCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                        $endOfCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                        $today = \Carbon\Carbon::today()->format('Y-m-d');

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

                    @for($week = $startOfCalendar; $week <= $endOfCalendar; $week->addWeek())
                        <tr>
                            @for($day = 0; $day < 7; $day++)
                                @php
                                    $currentDate = $week->copy()->addDays($day);
                                    $dateStr = $currentDate->format('Y-m-d');
                                    $isOtherMonth = $currentDate->month !== $currentMonth->month;
                                    $isToday = $dateStr === $today;
                                    $dayEvents = $eventsByDate[$dateStr] ?? [];
                                @endphp
                                <td>
                                    <div class="calendar-day {{ $isToday ? 'today' : '' }} {{ $isOtherMonth ? 'other-month' : '' }}">
                                        {{ $currentDate->day }}
                                    </div>
                                    @foreach(array_slice($dayEvents, 0, 3) as $event)
                                        <a href="{{ route('events.show', $event['id']) }}"
                                           class="calendar-event d-block text-decoration-none"
                                           style="background-color: {{ $categoryColors[$event['category']] ?? '#666' }}"
                                           title="{{ $event['title'] }}">
                                            {{ $event['title'] }}
                                        </a>
                                    @endforeach
                                    @if(count($dayEvents) > 3)
                                        <small class="text-muted">+{{ count($dayEvents) - 3 }}件</small>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="col-lg-3">
            <!-- Event List for Selected Month -->
            <div class="sticky-top" style="top: 20px;">
                <h5 class="mb-3">今月のイベント ({{ count($events) }}件)</h5>

                @if(count($events) > 0)
                    @foreach($events as $event)
                        <div class="event-list-item"
                             style="border-color: {{ $categoryColors[$event['category']] ?? '#666' }}">
                            <div class="small text-muted mb-1">
                                {{ date('m/d', strtotime($event['start_date'])) }}
                                @if(($event['end_date'] ?? $event['start_date']) !== $event['start_date'])
                                    〜{{ date('m/d', strtotime($event['end_date'])) }}
                                @endif
                            </div>
                            <a href="{{ route('events.show', $event['id']) }}"
                               class="text-decoration-none text-dark fw-bold">
                                {{ $event['title'] }}
                            </a>
                            <div class="small text-muted">
                                <i class="bi bi-geo-alt"></i> {{ $event['venue'] }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x display-4"></i>
                        <p class="mt-2">この月のイベントはありません</p>
                    </div>
                @endif

                <a href="{{ route('events.search', ['region' => $region]) }}"
                   class="btn btn-outline-primary w-100 mt-3">
                    リスト表示で見る
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
