@extends('layouts.app')

@section('title', '顧客詳細')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>顧客詳細</h2>
    <a href="{{ route('customers.index') }}" class="btn" style="background-color: #95a5a6; color: white;">戻る</a>
</div>

<div class="card">
    <table>
        <tr>
            <th style="width: 200px;">ID</th>
            <td>{{ $customer->id }}</td>
        </tr>
        <tr>
            <th>氏名</th>
            <td>{{ $customer->name }}</td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td>{{ $customer->email }}</td>
        </tr>
        <tr>
            <th>電話番号</th>
            <td>{{ $customer->phone ?? '-' }}</td>
        </tr>
        <tr>
            <th>住所</th>
            <td>{{ $customer->address ?? '-' }}</td>
        </tr>
        <tr>
            <th>登録日時</th>
            <td>{{ $customer->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <th>更新日時</th>
            <td>{{ $customer->updated_at->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    <div style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1rem;">予約履歴</h3>
        @if($customer->reservations->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>予約ID</th>
                        <th>宿泊施設</th>
                        <th>部屋番号</th>
                        <th>チェックイン</th>
                        <th>チェックアウト</th>
                        <th>ステータス</th>
                        <th>合計金額</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->id }}</td>
                        <td>{{ $reservation->room->accommodation->name }}</td>
                        <td>{{ $reservation->room->room_number }}</td>
                        <td>{{ $reservation->check_in_date->format('Y-m-d') }}</td>
                        <td>{{ $reservation->check_out_date->format('Y-m-d') }}</td>
                        <td>{{ $reservation->status }}</td>
                        <td>¥{{ number_format($reservation->total_amount) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>予約履歴はありません。</p>
        @endif
    </div>

    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-success">編集</a>
        <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
        </form>
    </div>
</div>
@endsection
