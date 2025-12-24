@extends('layouts.app')

@section('title', '予約一覧')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>予約一覧</h2>
    <a href="{{ route('reservations.create') }}" class="btn btn-primary">新規登録</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>顧客名</th>
                <th>宿泊施設</th>
                <th>部屋番号</th>
                <th>チェックイン</th>
                <th>チェックアウト</th>
                <th>ステータス</th>
                <th>合計金額</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $reservation)
            <tr>
                <td>{{ $reservation->id }}</td>
                <td>{{ $reservation->customer->name }}</td>
                <td>{{ $reservation->room->accommodation->name }}</td>
                <td>{{ $reservation->room->room_number }}</td>
                <td>{{ $reservation->check_in_date->format('Y-m-d') }}</td>
                <td>{{ $reservation->check_out_date->format('Y-m-d') }}</td>
                <td>{{ $reservation->status }}</td>
                <td>¥{{ number_format($reservation->total_amount) }}</td>
                <td>
                    <a href="{{ route('reservations.show', $reservation) }}" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">詳細</a>
                    <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-success" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">編集</a>
                    <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;" onclick="return confirm('本当に削除しますか？')">削除</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center;">予約が登録されていません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $reservations->links() }}
    </div>
</div>
@endsection
