@extends('layouts.app')

@section('title', 'レビュー編集')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('reviews.show', $review) }}" class="btn btn-primary">← 詳細に戻る</a>
</div>

<div class="card">
    <h2 style="margin-bottom: 2rem;">レビューを編集</h2>

    @if($errors->any())
        <div style="background-color: #fee; padding: 1rem; border-radius: 4px; border: 1px solid #e74c3c; margin-bottom: 2rem;">
            <ul style="margin-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li style="color: #e74c3c;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">予約情報</h3>
        <p><strong>宿泊施設:</strong> {{ $review->reservation->room->accommodation->name }}</p>
        <p><strong>部屋番号:</strong> {{ $review->reservation->room->room_number }}</p>
        <p><strong>顧客:</strong> {{ $review->customer->name }}</p>
    </div>

    <form method="POST" action="{{ route('reviews.update', $review) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="overall_rating">総合評価 *</label>
            <select name="overall_rating" id="overall_rating" required style="width: auto;">
                <option value="5" {{ old('overall_rating', $review->overall_rating) == 5 ? 'selected' : '' }}>★★★★★ 5 - 最高</option>
                <option value="4" {{ old('overall_rating', $review->overall_rating) == 4 ? 'selected' : '' }}>★★★★☆ 4 - 良い</option>
                <option value="3" {{ old('overall_rating', $review->overall_rating) == 3 ? 'selected' : '' }}>★★★☆☆ 3 - 普通</option>
                <option value="2" {{ old('overall_rating', $review->overall_rating) == 2 ? 'selected' : '' }}>★★☆☆☆ 2 - 改善が必要</option>
                <option value="1" {{ old('overall_rating', $review->overall_rating) == 1 ? 'selected' : '' }}>★☆☆☆☆ 1 - 悪い</option>
            </select>
        </div>

        <h3 style="margin: 2rem 0 1rem;">詳細評価</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="cleanliness_rating">清潔さ</label>
                <select name="cleanliness_rating" id="cleanliness_rating">
                    <option value="">-</option>
                    <option value="5" {{ old('cleanliness_rating', $review->cleanliness_rating) == 5 ? 'selected' : '' }}>★★★★★ 5</option>
                    <option value="4" {{ old('cleanliness_rating', $review->cleanliness_rating) == 4 ? 'selected' : '' }}>★★★★☆ 4</option>
                    <option value="3" {{ old('cleanliness_rating', $review->cleanliness_rating) == 3 ? 'selected' : '' }}>★★★☆☆ 3</option>
                    <option value="2" {{ old('cleanliness_rating', $review->cleanliness_rating) == 2 ? 'selected' : '' }}>★★☆☆☆ 2</option>
                    <option value="1" {{ old('cleanliness_rating', $review->cleanliness_rating) == 1 ? 'selected' : '' }}>★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="service_rating">サービス</label>
                <select name="service_rating" id="service_rating">
                    <option value="">-</option>
                    <option value="5" {{ old('service_rating', $review->service_rating) == 5 ? 'selected' : '' }}>★★★★★ 5</option>
                    <option value="4" {{ old('service_rating', $review->service_rating) == 4 ? 'selected' : '' }}>★★★★☆ 4</option>
                    <option value="3" {{ old('service_rating', $review->service_rating) == 3 ? 'selected' : '' }}>★★★☆☆ 3</option>
                    <option value="2" {{ old('service_rating', $review->service_rating) == 2 ? 'selected' : '' }}>★★☆☆☆ 2</option>
                    <option value="1" {{ old('service_rating', $review->service_rating) == 1 ? 'selected' : '' }}>★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="location_rating">立地</label>
                <select name="location_rating" id="location_rating">
                    <option value="">-</option>
                    <option value="5" {{ old('location_rating', $review->location_rating) == 5 ? 'selected' : '' }}>★★★★★ 5</option>
                    <option value="4" {{ old('location_rating', $review->location_rating) == 4 ? 'selected' : '' }}>★★★★☆ 4</option>
                    <option value="3" {{ old('location_rating', $review->location_rating) == 3 ? 'selected' : '' }}>★★★☆☆ 3</option>
                    <option value="2" {{ old('location_rating', $review->location_rating) == 2 ? 'selected' : '' }}>★★☆☆☆ 2</option>
                    <option value="1" {{ old('location_rating', $review->location_rating) == 1 ? 'selected' : '' }}>★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="value_rating">価格</label>
                <select name="value_rating" id="value_rating">
                    <option value="">-</option>
                    <option value="5" {{ old('value_rating', $review->value_rating) == 5 ? 'selected' : '' }}>★★★★★ 5</option>
                    <option value="4" {{ old('value_rating', $review->value_rating) == 4 ? 'selected' : '' }}>★★★★☆ 4</option>
                    <option value="3" {{ old('value_rating', $review->value_rating) == 3 ? 'selected' : '' }}>★★★☆☆ 3</option>
                    <option value="2" {{ old('value_rating', $review->value_rating) == 2 ? 'selected' : '' }}>★★☆☆☆ 2</option>
                    <option value="1" {{ old('value_rating', $review->value_rating) == 1 ? 'selected' : '' }}>★☆☆☆☆ 1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="amenities_rating">設備</label>
                <select name="amenities_rating" id="amenities_rating">
                    <option value="">-</option>
                    <option value="5" {{ old('amenities_rating', $review->amenities_rating) == 5 ? 'selected' : '' }}>★★★★★ 5</option>
                    <option value="4" {{ old('amenities_rating', $review->amenities_rating) == 4 ? 'selected' : '' }}>★★★★☆ 4</option>
                    <option value="3" {{ old('amenities_rating', $review->amenities_rating) == 3 ? 'selected' : '' }}>★★★☆☆ 3</option>
                    <option value="2" {{ old('amenities_rating', $review->amenities_rating) == 2 ? 'selected' : '' }}>★★☆☆☆ 2</option>
                    <option value="1" {{ old('amenities_rating', $review->amenities_rating) == 1 ? 'selected' : '' }}>★☆☆☆☆ 1</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="title">タイトル</label>
            <input type="text" name="title" id="title" value="{{ old('title', $review->title) }}">
        </div>

        <div class="form-group">
            <label for="comment">コメント</label>
            <textarea name="comment" id="comment" rows="5">{{ old('comment', $review->comment) }}</textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-success">更新</button>
            <a href="{{ route('reviews.show', $review) }}" class="btn btn-primary">キャンセル</a>
        </div>
    </form>
</div>
@endsection
