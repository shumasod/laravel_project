@extends('layouts.app')

@section('title', '宿泊施設詳細')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>宿泊施設詳細</h2>
    <a href="{{ route('accommodations.index') }}" class="btn" style="background-color: #95a5a6; color: white;">戻る</a>
</div>

<div class="card">
    <table>
        <tr>
            <th style="width: 200px;">ID</th>
            <td>{{ $accommodation->id }}</td>
        </tr>
        <tr>
            <th>施設名</th>
            <td>{{ $accommodation->name }}</td>
        </tr>
        <tr>
            <th>住所</th>
            <td>{{ $accommodation->address }}</td>
        </tr>
        <tr>
            <th>説明</th>
            <td>{{ $accommodation->description ?? '-' }}</td>
        </tr>
        <tr>
            <th>電話番号</th>
            <td>{{ $accommodation->phone ?? '-' }}</td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td>{{ $accommodation->email ?? '-' }}</td>
        </tr>
        <tr>
            <th>登録日時</th>
            <td>{{ $accommodation->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <th>更新日時</th>
            <td>{{ $accommodation->updated_at->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    <div style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1rem;">登録部屋</h3>
        @if($accommodation->rooms->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>部屋番号</th>
                        <th>部屋タイプ</th>
                        <th>1泊料金</th>
                        <th>定員</th>
                        <th>状態</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accommodation->rooms as $room)
                    <tr>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->room_type }}</td>
                        <td>¥{{ number_format($room->price_per_night) }}</td>
                        <td>{{ $room->capacity }}名</td>
                        <td>{{ $room->is_available ? '利用可能' : '利用不可' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>登録された部屋はありません。</p>
        @endif
    </div>

    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="{{ route('accommodations.edit', $accommodation) }}" class="btn btn-success">編集</a>
        <form action="{{ route('accommodations.destroy', $accommodation) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
        </form>
    </div>
</div>
@endsection
