@extends('layouts.app')

@section('title', 'レビュー詳細')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('reviews.index') }}" class="btn btn-primary">← 一覧に戻る</a>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
        <div>
            <h2 style="margin-bottom: 0.5rem;">{{ $review->title ?? 'レビュー #' . $review->id }}</h2>
            <div style="color: #7f8c8d;">
                {{ $review->created_at->format('Y年m月d日') }} - {{ $review->customer->name }}
            </div>
        </div>
        <div style="text-align: right;">
            @if($review->is_published)
                <span style="background-color: #27ae60; color: white; padding: 0.3rem 0.8rem; border-radius: 4px;">公開</span>
            @else
                <span style="background-color: #95a5a6; color: white; padding: 0.3rem 0.8rem; border-radius: 4px;">非公開</span>
            @endif
            @if($review->is_verified)
                <span style="background-color: #3498db; color: white; padding: 0.3rem 0.8rem; border-radius: 4px; margin-left: 0.5rem;">✓ 認証済み</span>
            @endif
        </div>
    </div>

    <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">宿泊情報</h3>
        <p><strong>宿泊施設:</strong> {{ $review->accommodation->name }}</p>
        <p><strong>予約ID:</strong> <a href="{{ route('reservations.show', $review->reservation) }}" style="color: #3498db;">#{{ $review->reservation_id }}</a></p>
    </div>

    <div style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">評価</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 4px; text-align: center;">
                <div style="font-size: 2rem; color: #f39c12; font-weight: bold;">{{ $review->overall_rating }}</div>
                <div style="color: #7f8c8d;">総合評価</div>
            </div>
            @if($review->cleanliness_rating)
                <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 4px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $review->cleanliness_rating }}</div>
                    <div style="color: #7f8c8d;">清潔さ</div>
                </div>
            @endif
            @if($review->service_rating)
                <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 4px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $review->service_rating }}</div>
                    <div style="color: #7f8c8d;">サービス</div>
                </div>
            @endif
            @if($review->location_rating)
                <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 4px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $review->location_rating }}</div>
                    <div style="color: #7f8c8d;">立地</div>
                </div>
            @endif
            @if($review->value_rating)
                <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 4px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $review->value_rating }}</div>
                    <div style="color: #7f8c8d;">価格</div>
                </div>
            @endif
            @if($review->amenities_rating)
                <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 4px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $review->amenities_rating }}</div>
                    <div style="color: #7f8c8d;">設備</div>
                </div>
            @endif
        </div>
    </div>

    @if($review->comment)
        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">コメント</h3>
            <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px; line-height: 1.8;">
                {{ $review->comment }}
            </div>
        </div>
    @endif

    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
        <span style="color: #7f8c8d;">このレビューは役に立ちましたか？</span>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="color: #3498db; font-weight: bold;">{{ $review->helpful_count }}</span>
            <span style="color: #7f8c8d;">人が「役に立った」と評価</span>
        </div>
    </div>

    @if($review->admin_response)
        <div style="background-color: #e8f4f8; padding: 1.5rem; border-radius: 4px; border-left: 4px solid #3498db; margin-bottom: 2rem;">
            <h4 style="margin-bottom: 0.5rem; color: #3498db;">施設からの返信</h4>
            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1rem;">
                {{ $review->admin_responded_at->format('Y年m月d日') }}
            </p>
            <div style="line-height: 1.8;">{{ $review->admin_response }}</div>
        </div>
    @else
        <div style="margin-bottom: 2rem;">
            <button type="button" class="btn btn-primary" onclick="document.getElementById('adminResponseForm').style.display='block'">
                施設から返信する
            </button>
        </div>

        <div id="adminResponseForm" style="display: none; background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem;">
            <h4 style="margin-bottom: 1rem;">施設からの返信</h4>
            <form method="POST" action="{{ route('reviews.admin-response', $review) }}">
                @csrf
                <div class="form-group">
                    <textarea name="admin_response" rows="4" required placeholder="返信内容を入力してください"></textarea>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-success">返信を投稿</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('adminResponseForm').style.display='none'">キャンセル</button>
                </div>
            </form>
        </div>
    @endif

    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('reviews.edit', $review) }}" class="btn btn-primary">編集</a>
        <form method="POST" action="{{ route('reviews.destroy', $review) }}" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
        </form>
    </div>
</div>
@endsection
