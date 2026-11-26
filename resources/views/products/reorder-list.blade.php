@extends('layouts.app')

@section('title', '発注リスト')

@section('content')
<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-clipboard2-check me-2"></i>発注リスト</h2>
        <div>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>印刷
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left me-1"></i>戻る
            </a>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>現在、発注が必要な商品はありません。
        </div>
    @else
        <p class="text-muted">発注点を下回っている商品: <strong>{{ $products->count() }}件</strong></p>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>商品名</th>
                            <th class="text-end">現在在庫</th>
                            <th class="text-end">発注点</th>
                            <th class="text-end">推奨発注数</th>
                            <th class="no-print">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr class="{{ $product->isOutOfStock() ? 'table-danger' : 'table-warning' }}">
                            <td><code>{{ $product->sku }}</code></td>
                            <td>{{ $product->name }}</td>
                            <td class="text-end fw-bold">{{ number_format($product->stock_quantity) }}</td>
                            <td class="text-end text-muted">{{ number_format($product->reorder_point) }}</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($product->order_quantity) }}</td>
                            <td class="no-print">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>入庫登録
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    nav, .navbar, .btn { display: none !important; }
}
</style>
@endpush
