@extends('layouts.app')

@section('title', '部屋一覧')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>部屋一覧</h2>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary">新規登録</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>宿泊施設</th>
                <th>部屋番号</th>
                <th>部屋タイプ</th>
                <th>1泊料金</th>
                <th>定員</th>
                <th>状態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $room)
            <tr>
                <td>{{ $room->id }}</td>
                <td>{{ $room->accommodation->name }}</td>
                <td>{{ $room->room_number }}</td>
                <td>{{ $room->room_type }}</td>
                <td>¥{{ number_format($room->price_per_night) }}</td>
                <td>{{ $room->capacity }}名</td>
                <td>{{ $room->is_available ? '利用可能' : '利用不可' }}</td>
                <td>
                    <a href="{{ route('rooms.show', $room) }}" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">詳細</a>
                    <a href="{{ route('rooms.edit', $room) }}" class="btn btn-success" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">編集</a>
                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;" onclick="return confirm('本当に削除しますか？')">削除</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">部屋が登録されていません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $rooms->links() }}
    </div>
</div>
@endsection
