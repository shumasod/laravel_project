@extends('layouts.app')

@section('title', '顧客登録')

@section('content')
<h2 style="margin-bottom: 2rem;">顧客登録</h2>

<div class="card">
    <form action="{{ route('customers.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">氏名 *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">メールアドレス *</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">電話番号</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
            @error('phone')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="address">住所</label>
            <textarea id="address" name="address">{{ old('address') }}</textarea>
            @error('address')
                <span style="color: red; font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">登録</button>
            <a href="{{ route('customers.index') }}" class="btn" style="background-color: #95a5a6; color: white;">キャンセル</a>
        </div>
    </form>
</div>
@endsection
