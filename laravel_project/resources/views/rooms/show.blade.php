@extends('layouts.app')

@section('title', '部屋詳細')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>部屋詳細</h2>
    <a href="{{ route('rooms.index') }}" class="btn" style="background-color: #95a5a6; color: white;">戻る</a>
</div>

<div class="card">
    <table>
        <tr>
            <th style="width: 200px;">ID</th>
            <td>{{ $room->id }}</td>
        </tr>
        <tr>
            <th>宿泊施設</th>
            <td>{{ $room->accommodation->name }}</td>
        </tr>
        <tr>
            <th>部屋番号</th>
            <td>{{ $room->room_number }}</td>
        </tr>
        <tr>
            <th>部屋タイプ</th>
            <td>{{ $room->room_type }}</td>
        </tr>
        <tr>
            <th>1泊料金</th>
            <td>¥{{ number_format($room->price_per_night) }}</td>
        </tr>
        <tr>
            <th>定員</th>
            <td>{{ $room->capacity }}名</td>
        </tr>
        <tr>
            <th>説明</th>
            <td>{{ $room->description ?? '-' }}</td>
        </tr>
        <tr>
            <th>状態</th>
            <td>{{ $room->is_available ? '利用可能' : '利用不可' }}</td>
        </tr>
        <tr>
            <th>登録日時</th>
            <td>{{ $room->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <th>更新日時</th>
            <td>{{ $room->updated_at->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-success">編集</a>
        <form action="{{ route('rooms.destroy', $room) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
        </form>
    </div>
</div>
@endsection
