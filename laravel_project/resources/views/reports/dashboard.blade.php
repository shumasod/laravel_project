@extends('layouts.app')

@section('title', 'ダッシュボード')

@section('content')
<h2 style="margin-bottom: 2rem;">レポート・ダッシュボード</h2>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold; color: #3498db;">{{ $report['reservations']['total_reservations'] }}</div>
        <div style="color: #7f8c8d; margin-top: 0.5rem;">予約総数</div>
    </div>
    <div class="card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold; color: #27ae60;">{{ $report['reservations']['completed'] }}</div>
        <div style="color: #7f8c8d; margin-top: 0.5rem;">完了</div>
    </div>
    <div class="card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold; color: #f39c12;">{{ $report['reservations']['confirmed'] }}</div>
        <div style="color: #7f8c8d; margin-top: 0.5rem;">確定済み</div>
    </div>
    <div class="card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;">{{ $report['reservations']['cancellation_rate'] }}%</div>
        <div style="color: #7f8c8d; margin-top: 0.5rem;">キャンセル率</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 2rem;">
    <div class="card">
        <h3 style="margin-bottom: 1rem;">売上概要</h3>
        <table style="box-shadow: none;">
            <tr>
                <th style="background-color: #f8f9fa; color: #333; width: 40%;">総売上</th>
                <td style="font-size: 1.5rem; font-weight: bold; color: #27ae60;">¥{{ number_format($report['revenue']['total_revenue']) }}</td>
            </tr>
            <tr>
                <th style="background-color: #f8f9fa; color: #333;">取引件数</th>
                <td>{{ $report['revenue']['total_transactions'] }}件</td>
            </tr>
            <tr>
                <th style="background-color: #f8f9fa; color: #333;">平均単価</th>
                <td>¥{{ number_format($report['revenue']['average_transaction_value']) }}</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 1rem;">レビュー</h3>
        <div style="text-align: center; padding: 1rem 0;">
            <div style="font-size: 3rem; font-weight: bold; color: #f39c12;">{{ $report['reviews']['average_rating'] }}</div>
            <div style="color: #7f8c8d; font-size: 1.2rem;">平均評価</div>
            <div style="color: #7f8c8d; margin-top: 0.5rem;">{{ $report['reviews']['total_reviews'] }}件のレビュー</div>
        </div>
    </div>
</div>

@if(isset($report['occupancy']))
<div class="card" style="margin-bottom: 2rem;">
    <h3 style="margin-bottom: 1rem;">占有率</h3>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
        <div style="text-align: center; padding: 1rem; background-color: #f8f9fa; border-radius: 4px;">
            <div style="font-size: 1.5rem; font-weight: bold; color: #3498db;">{{ $report['occupancy']['occupancy_rate'] }}%</div>
            <div style="color: #7f8c8d; margin-top: 0.5rem;">占有率</div>
        </div>
        <div style="text-align: center; padding: 1rem; background-color: #f8f9fa; border-radius: 4px;">
            <div style="font-size: 1.5rem; font-weight: bold;">{{ $report['occupancy']['total_rooms'] }}</div>
            <div style="color: #7f8c8d; margin-top: 0.5rem;">総部屋数</div>
        </div>
        <div style="text-align: center; padding: 1rem; background-color: #f8f9fa; border-radius: 4px;">
            <div style="font-size: 1.5rem; font-weight: bold;">{{ $report['occupancy']['occupied_room_nights'] }}</div>
            <div style="color: #7f8c8d; margin-top: 0.5rem;">稼働室数</div>
        </div>
        <div style="text-align: center; padding: 1rem; background-color: #f8f9fa; border-radius: 4px;">
            <div style="font-size: 1.5rem; font-weight: bold;">{{ $report['occupancy']['total_room_nights'] }}</div>
            <div style="color: #7f8c8d; margin-top: 0.5rem;">総室数</div>
        </div>
    </div>
</div>
@endif

<div class="card" style="margin-bottom: 2rem;">
    <h3 style="margin-bottom: 1rem;">顧客統計</h3>
    <table style="box-shadow: none;">
        <tr>
            <th style="background-color: #f8f9fa; color: #333; width: 40%;">総顧客数</th>
            <td>{{ $report['customers']['total_customers'] }}人</td>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; color: #333;">リピーター数</th>
            <td>{{ $report['customers']['repeat_customers'] }}人</td>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; color: #333;">リピーター率</th>
            <td style="font-weight: bold; color: #3498db;">{{ $report['customers']['repeat_rate'] }}%</td>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; color: #333;">平均宿泊日数</th>
            <td>{{ $report['customers']['average_stay_duration'] }}泊</td>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; color: #333;">平均ゲスト数</th>
            <td>{{ $report['customers']['average_guest_count'] }}人</td>
        </tr>
    </table>
</div>

<div style="display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="{{ route('reports.reservations') }}" class="btn btn-primary">予約レポート</a>
    <a href="{{ route('reports.revenue') }}" class="btn btn-primary">売上レポート</a>
    @if($accommodationId)
        <a href="{{ route('reports.occupancy') }}?accommodation_id={{ $accommodationId }}" class="btn btn-primary">占有率レポート</a>
    @endif
    <a href="{{ route('reports.reviews') }}" class="btn btn-primary">レビューレポート</a>
    <a href="{{ route('reports.customers') }}" class="btn btn-primary">顧客レポート</a>
</div>
@endsection
