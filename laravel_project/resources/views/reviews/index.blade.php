@extends('layouts.app')

@section('title', 'レビュー一覧')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>レビュー一覧</h2>
    <a href="{{ route('reviews.create') }}" class="btn btn-success">新規レビュー</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>宿泊施設</th>
                <th>顧客名</th>
                <th>評価</th>
                <th>タイトル</th>
                <th>ステータス</th>
                <th>投稿日</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->id }}</td>
                    <td>{{ $review->accommodation->name }}</td>
                    <td>{{ $review->customer->name }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #f39c12; font-size: 1.2rem;">★</span>
                            <strong>{{ $review->overall_rating }}</strong>
                            <span style="color: #7f8c8d;">/5</span>
                        </div>
                    </td>
                    <td>{{ $review->title ?? '-' }}</td>
                    <td>
                        @if($review->is_published)
                            <span style="color: #27ae60;">公開</span>
                        @else
                            <span style="color: #95a5a6;">非公開</span>
                        @endif
                        @if($review->is_verified)
                            <span style="color: #3498db; margin-left: 0.5rem;">✓認証済み</span>
                        @endif
                    </td>
                    <td>{{ $review->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('reviews.show', $review) }}" class="btn btn-primary" style="font-size: 0.9rem; padding: 0.3rem 0.8rem;">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem;">レビューがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($reviews->hasPages())
        <div style="margin-top: 2rem;">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection
