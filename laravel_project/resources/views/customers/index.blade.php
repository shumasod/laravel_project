@extends('layouts.app')

@section('title', '顧客一覧')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>顧客一覧</h2>
    <a href="{{ route('customers.create') }}" class="btn btn-primary">新規登録</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>氏名</th>
                <th>メールアドレス</th>
                <th>電話番号</th>
                <th>予約数</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr>
                <td>{{ $customer->id }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td>{{ $customer->phone ?? '-' }}</td>
                <td>{{ $customer->reservations->count() }}</td>
                <td>
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">詳細</a>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-success" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;">編集</a>
                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size: 0.875rem; padding: 0.25rem 0.5rem;" onclick="return confirm('本当に削除しますか？')">削除</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">顧客が登録されていません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $customers->links() }}
    </div>
</div>
@endsection
