@extends('layouts.app')

@section('title', 'レビュー投稿')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('reviews.index') }}" class="btn btn-primary">← 一覧に戻る</a>
</div>

<div class="card">
    <h2 style="margin-bottom: 2rem;">レビューを投稿</h2>

    @if($errors->any())
        <div style="background-color: #fee; padding: 1rem; border-radius: 4px; border: 1px solid #e74c3c; margin-bottom: 2rem;">
            <ul style="margin-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li style="color: #e74c3c;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($reservation))
        <div style="background-color: #e8f4f8; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; color: #3498db;">予約情報</h3>
            <p><strong>宿泊施設:</strong> {{ $reservation->room->accommodation->name }}</p>
            <p><strong>部屋番号:</strong> {{ $reservation->room->room_number }}</p>
            <p><strong>宿泊期間:</strong> {{ $reservation->check_in_date->format('Y/m/d') }} - {{ $reservation->check_out_date->format('Y/m/d') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('reviews.store') }}">
        @csrf

        @if(isset($reservation))
            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
        @else
            <div class="form-group">
                <label for="reservation_id">予約 *</label>
                <select name="reservation_id" id="reservation_id" required>
                    <option value="">選択してください</option>
                </select>
                <small style="color: #7f8c8d;">チェックアウト済みの予約を選択してください</small>
            </div>
        @endif

        <div class="form-group">
            <label for="overall_rating">総合評価 *</label>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <select name="overall_rating" id="overall_rating" required style="width: auto;">
                    <option value="">選択</option>
                    <option value="5" {{ old('overall_rating') == 5 ? 'selected' : '' }}>★★★★★ 5 - 最高</option>
                    <option value="4" {{ old('overall_rating') == 4 ? 'selected' : '' }}>★★★★☆ 4 - 良い</option>
                    <option value="3" {{ old('overall_rating') == 3 ? 'selected' : '' }}>★★★☆☆ 3 - 普通</option>
                    <option value="2" {{ old('overall_rating') == 2 ? 'selected' : '' }}>★★☆☆☆ 2 - 改善が必要</option>
                    <option value="1" {{ old('overall_rating') == 1 ? 'selected' : '' }}>★☆☆☆☆ 1 - 悪い</option>
                </select>
            </div>
        </div>

        <h3 style="margin: 2rem 0 1rem;">詳細評価（任意）</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="cleanliness_rating">清潔さ</label>
                <select name="cleanliness_rating" id="cleanliness_rating">
                    <option value="">-</option>
                    <option value="5">★★★★★ 5</option>
                    <option value="4">★★★★☆ 4</option>
                    <option value="3">★★★☆☆ 3</option>
                    <option value="2">★★☆☆☆ 2</option>
                    <option value="1">★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="service_rating">サービス</label>
                <select name="service_rating" id="service_rating">
                    <option value="">-</option>
                    <option value="5">★★★★★ 5</option>
                    <option value="4">★★★★☆ 4</option>
                    <option value="3">★★★☆☆ 3</option>
                    <option value="2">★★☆☆☆ 2</option>
                    <option value="1">★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="location_rating">立地</label>
                <select name="location_rating" id="location_rating">
                    <option value="">-</option>
                    <option value="5">★★★★★ 5</option>
                    <option value="4">★★★★☆ 4</option>
                    <option value="3">★★★☆☆ 3</option>
                    <option value="2">★★☆☆☆ 2</option>
                    <option value="1">★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="value_rating">価格</label>
                <select name="value_rating" id="value_rating">
                    <option value="">-</option>
                    <option value="5">★★★★★ 5</option>
                    <option value="4">★★★★☆ 4</option>
                    <option value="3">★★★☆☆ 3</option>
                    <option value="2">★★☆☆☆ 2</option>
                    <option value="1">★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="amenities_rating">設備</label>
                <select name="amenities_rating" id="amenities_rating">
                    <option value="">-</option>
                    <option value="5">★★★★★ 5</option>
                    <option value="4">★★★★☆ 4</option>
                    <option value="3">★★★☆☆ 3</option>
                    <option value="2">★★☆☆☆ 2</option>
                    <option value="1">★☆☆☆☆ 1</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="title">タイトル</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="素晴らしい滞在でした！">
        </div>

        <div class="form-group">
            <label for="comment">コメント</label>
            <textarea name="comment" id="comment" rows="5" placeholder="宿泊の感想を詳しくお聞かせください（2000文字以内）">{{ old('comment') }}</textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-success">レビューを投稿</button>
            <a href="{{ route('reviews.index') }}" class="btn btn-primary">キャンセル</a>
        </div>
    </form>
</div>
@endsection
