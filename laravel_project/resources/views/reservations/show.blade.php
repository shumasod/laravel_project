@extends('layouts.app')

@section('title', '予約詳細')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>予約詳細</h2>
    <a href="{{ route('reservations.index') }}" class="btn" style="background-color: #95a5a6; color: white;">戻る</a>
</div>

<div class="card">
    <table>
        <tr>
            <th style="width: 200px;">ID</th>
            <td>{{ $reservation->id }}</td>
        </tr>
        <tr>
            <th>顧客名</th>
            <td>{{ $reservation->customer->name }}</td>
        </tr>
        <tr>
            <th>顧客メール</th>
            <td>{{ $reservation->customer->email }}</td>
        </tr>
        <tr>
            <th>宿泊施設</th>
            <td>{{ $reservation->room->accommodation->name }}</td>
        </tr>
        <tr>
            <th>部屋番号</th>
            <td>{{ $reservation->room->room_number }}</td>
        </tr>
        <tr>
            <th>部屋タイプ</th>
            <td>{{ $reservation->room->room_type }}</td>
        </tr>
        <tr>
            <th>チェックイン日</th>
            <td>{{ $reservation->check_in_date->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>チェックアウト日</th>
            <td>{{ $reservation->check_out_date->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>宿泊日数</th>
            <td>{{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }}泊</td>
        </tr>
        <tr>
            <th>ステータス</th>
            <td>{{ $reservation->status }}</td>
        </tr>
        <tr>
            <th>合計金額</th>
            <td>¥{{ number_format($reservation->total_amount) }}</td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{ $reservation->notes ?? '-' }}</td>
        </tr>
        <tr>
            <th>登録日時</th>
            <td>{{ $reservation->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <th>更新日時</th>
            <td>{{ $reservation->updated_at->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-success">編集</a>
        <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
        </form>
    </div>
</div>
@endsection
