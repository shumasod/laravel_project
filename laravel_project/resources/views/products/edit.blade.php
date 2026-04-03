@extends('layouts.app')

@section('title', $product->name . ' - 編集')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-pencil me-2"></i>商品編集</h2>
    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>詳細へ
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

        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                    value="{{ old('sku', $product->sku) }}" required maxlength="100">
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">商品名 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $product->name) }}" required maxlength="255">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">説明</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">発注点 <span class="text-danger">*</span></label>
                <input type="number" name="reorder_point" class="form-control"
                    value="{{ old('reorder_point', $product->reorder_point) }}" min="0" required>
                <div class="form-text">在庫数がこの値以下になると警告を表示します</div>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">現在の在庫数</label>
                <p class="fs-5 fw-bold">{{ number_format($product->stock_quantity) }}
                    <small class="text-muted fw-normal">（在庫数の変更は在庫操作から行ってください）</small>
                </p>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2 me-1"></i>更新する
            </button>
        </form>
    </div>
</div>
@endsection
