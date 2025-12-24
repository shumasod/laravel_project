@extends('layouts.app')

@section('title', '部屋登録')

@section('content')
<h2 style="margin-bottom: 2rem;">部屋登録</h2>

<div class="card">
    <form action="{{ route('rooms.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="accommodation_id">宿泊施設 *</label>
            <select id="accommodation_id" name="accommodation_id" required>
                <option value="">選択してください</option>
                @foreach($accommodations as $accommodation)
                    <option value="{{ $accommodation->id }}" {{ old('accommodation_id') == $accommodation->id ? 'selected' : '' }}>
                        {{ $accommodation->name }}
                    </option>
                @endforeach
            </select>
            @error('accommodation_id')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="room_number">部屋番号 *</label>
            <input type="text" id="room_number" name="room_number" value="{{ old('room_number') }}" required>
            @error('room_number')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="room_type">部屋タイプ *</label>
            <input type="text" id="room_type" name="room_type" value="{{ old('room_type') }}" required>
            @error('room_type')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="price_per_night">1泊料金 *</label>
            <input type="number" id="price_per_night" name="price_per_night" value="{{ old('price_per_night') }}" min="0" step="0.01" required>
            @error('price_per_night')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="capacity">定員 *</label>
            <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}" min="1" required>
            @error('capacity')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">説明</label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>
            @error('description')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}>
                利用可能
            </label>
            @error('is_available')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">登録</button>
            <a href="{{ route('rooms.index') }}" class="btn" style="background-color: #95a5a6; color: white;">キャンセル</a>
        </div>
    </form>
</div>
@endsection
