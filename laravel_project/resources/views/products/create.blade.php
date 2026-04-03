@extends('layouts.app')

@section('title', '商品登録')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-plus-circle me-2"></i>商品登録</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>一覧へ
    </a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                    value="{{ old('sku') }}" required maxlength="100" placeholder="例: ITEM-001">
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">商品名 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" required maxlength="255">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">説明</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="form-label fw-semibold">初期在庫数 <span class="text-danger">*</span></label>
                    <input type="number" name="stock_quantity" class="form-control"
                        value="{{ old('stock_quantity', 0) }}" min="0" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold">発注点 <span class="text-danger">*</span></label>
                    <input type="number" name="reorder_point" class="form-control"
                        value="{{ old('reorder_point', 10) }}" min="0" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2 me-1"></i>登録する
            </button>
        </form>
    </div>
</div>
@endsection
