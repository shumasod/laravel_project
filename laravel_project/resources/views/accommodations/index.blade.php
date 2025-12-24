@extends('layouts.app')

@section('title', '宿泊施設一覧')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>宿泊施設一覧</h2>
    <a href="{{ route('accommodations.create') }}" class="btn btn-primary">新規登録</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>施設名</th>
                <th>住所</th>
                <th>電話番号</th>
                <th>メール</th>
                <th>部屋数</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accommodations as $accommodation)
            <tr>
                <td>{{ $accommodation->id }}</td>
                <td>{{ $accommodation->name }}</td>
                <td>{{ $accommodation->address }}</td>
                <td>{{ $accommodation->phone ?? '-' }}</td>
                <td>{{ $accommodation->email ?? '-' }}</td>
                <td>{{ $accommodation->rooms->count() }}</td>
                <td>
                    <a href="{{ route('accommodations.show', $accommodation) }}" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">詳細</a>
                    <a href="{{ route('accommodations.edit', $accommodation) }}" class="btn btn-success" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">編集</a>
                    <form action="{{ route('accommodations.destroy', $accommodation) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;" onclick="return confirm('本当に削除しますか？')">削除</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">宿泊施設が登録されていません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $accommodations->links() }}
    </div>
</div>
@endsection
