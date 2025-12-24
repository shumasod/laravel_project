@extends('layouts.app')

@section('title', '予約編集')

@section('content')
<h2 style="margin-bottom: 2rem;">予約編集</h2>

<div class="card">
    <form action="{{ route('reservations.update', $reservation) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="customer_id">顧客 *</label>
            <select id="customer_id" name="customer_id" required>
                <option value="">選択してください</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $reservation->customer_id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ({{ $customer->email }})
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="room_id">部屋 *</label>
            <select id="room_id" name="room_id" required>
                <option value="">選択してください</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ old('room_id', $reservation->room_id) == $room->id ? 'selected' : '' }}>
                        {{ $room->accommodation->name }} - {{ $room->room_number }} ({{ $room->room_type }})
                    </option>
                @endforeach
            </select>
            @error('room_id')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="check_in_date">チェックイン日 *</label>
            <input type="date" id="check_in_date" name="check_in_date" value="{{ old('check_in_date', $reservation->check_in_date->format('Y-m-d')) }}" required>
            @error('check_in_date')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="check_out_date">チェックアウト日 *</label>
            <input type="date" id="check_out_date" name="check_out_date" value="{{ old('check_out_date', $reservation->check_out_date->format('Y-m-d')) }}" required>
            @error('check_out_date')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">ステータス *</label>
            <select id="status" name="status" required>
                <option value="pending" {{ old('status', $reservation->status) == 'pending' ? 'selected' : '' }}>保留中</option>
                <option value="confirmed" {{ old('status', $reservation->status) == 'confirmed' ? 'selected' : '' }}>確定</option>
                <option value="cancelled" {{ old('status', $reservation->status) == 'cancelled' ? 'selected' : '' }}>キャンセル</option>
                <option value="completed" {{ old('status', $reservation->status) == 'completed' ? 'selected' : '' }}>完了</option>
            </select>
            @error('status')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="total_amount">合計金額 *</label>
            <input type="number" id="total_amount" name="total_amount" value="{{ old('total_amount', $reservation->total_amount) }}" min="0" step="0.01" required>
            @error('total_amount')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="notes">備考</label>
            <textarea id="notes" name="notes">{{ old('notes', $reservation->notes) }}</textarea>
            @error('notes')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">更新</button>
            <a href="{{ route('reservations.index') }}" class="btn" style="background-color: #95a5a6; color: white;">キャンセル</a>
        </div>
    </form>
</div>
@endsection
