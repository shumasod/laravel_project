@extends('layouts.app')

@section('title', 'QRコード一括印刷')

@section('content')
<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-qr-code me-2"></i>QRコード一括印刷</h2>
        <div>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>印刷する
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left me-1"></i>戻る
            </a>
        </div>
    </div>
    <p class="text-muted">全商品 {{ $products->count() }}件 のQRコードを表示しています。</p>
    <div class="row g-3">
        @forelse($products as $product)
        <div class="col-md-3 col-sm-4 col-6">
            <div class="card text-center p-2 h-100">
                <img src="{{ route('products.qrcode', $product) }}"
                     width="150" height="150" class="mx-auto" alt="QR: {{ $product->sku }}">
                <div class="mt-2 fw-semibold small text-truncate">{{ $product->name }}</div>
                <div class="text-muted small"><code>{{ $product->sku }}</code></div>
                <div class="small mt-1">
                    @if($product->isOutOfStock())
                        <span class="badge bg-danger">在庫切れ</span>
                    @elseif($product->isBelowReorderPoint())
                        <span class="badge bg-warning text-dark">要発注</span>
                    @else
                        <span class="badge bg-success">正常</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <p class="text-muted">商品が登録されていません。</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    nav, .btn, .navbar, p.text-muted { display: none !important; }
    .card { border: 1px solid #333 !important; break-inside: avoid; }
    .badge { border: 1px solid #999; }
    h2 { font-size: 14pt; }
}
</style>
@endpush
