@extends('layouts.app')

@section('title', '決済作成')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('payments.index') }}" class="btn btn-primary">← 一覧に戻る</a>
</div>

<div class="card">
    <h2 style="margin-bottom: 2rem;">新規決済</h2>

    @if($errors->any())
        <div style="background-color: #fee; padding: 1rem; border-radius: 4px; border: 1px solid #e74c3c; margin-bottom: 2rem;">
            <ul style="margin-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li style="color: #e74c3c;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}">
        @csrf

        <div class="form-group">
            <label for="reservation_id">予約 *</label>
            <select name="reservation_id" id="reservation_id" required>
                <option value="">選択してください</option>
                @if(isset($reservation))
                    <option value="{{ $reservation->id }}" selected>
                        #{{ $reservation->id }} - {{ $reservation->customer->name }} ({{ $reservation->room->room_number }})
                    </option>
                @endif
            </select>
            <small style="color: #7f8c8d;">予約IDから選択してください</small>
        </div>

        <div class="form-group">
            <label for="amount">金額 *</label>
            <input type="number" name="amount" id="amount" step="0.01" required
                   value="{{ old('amount', $reservation->total_amount ?? '') }}"
                   placeholder="10000.00">
        </div>

        <div class="form-group">
            <label for="payment_method">決済方法 *</label>
            <select name="payment_method" id="payment_method" required>
                <option value="">選択してください</option>
                <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>クレジットカード</option>
                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>銀行振込</option>
                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>現金</option>
                <option value="digital_wallet" {{ old('payment_method') === 'digital_wallet' ? 'selected' : '' }}>デジタルウォレット</option>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_gateway">決済ゲートウェイ</label>
            <input type="text" name="payment_gateway" id="payment_gateway"
                   value="{{ old('payment_gateway') }}"
                   placeholder="stripe, paypal など">
        </div>

        <div class="form-group">
            <label for="notes">備考</label>
            <textarea name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="process_now" value="1" {{ old('process_now') ? 'checked' : '' }}>
                <span>作成後すぐに決済を処理する</span>
            </label>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success">作成</button>
            <a href="{{ route('payments.index') }}" class="btn btn-primary">キャンセル</a>
        </div>
    </form>
</div>
@endsection
