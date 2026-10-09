@extends('layouts.app')

@section('title', '在庫アラートダッシュボード')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>在庫アラートダッシュボード</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('products.reorder-list') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clipboard2-check me-1"></i>発注リスト
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-box-seam me-1"></i>商品一覧
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-danger text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-danger">{{ $stats['alert_count'] }}</div>
                <div class="text-muted small">アラート商品数</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-warning text-center">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning">{{ number_format($stats['total_deficit']) }}</div>
                <div class="text-muted small">総不足数（発注点との差）</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-secondary text-center">
            <div class="card-body py-3">
                @if($stats['most_critical'])
                <div class="fs-6 fw-bold">{{ $stats['most_critical']->name }}</div>
                <div class="text-danger small">在庫: {{ $stats['most_critical']->stock_quantity }} / 発注点: {{ $stats['most_critical']->reorder_point }}</div>
                @else
                <div class="text-muted">—</div>
                @endif
                <div class="text-muted small mt-1">最も深刻な商品</div>
            </div>
        </div>
    </div>
</div>

@if($alertProducts->isEmpty())
<div class="alert alert-success">
    <i class="bi bi-check-circle me-1"></i>現在アラート中の商品はありません。
</div>
@else
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-danger">
                <tr>
                    <th>SKU</th>
                    <th>商品名</th>
                    <th class="text-end">現在庫</th>
                    <th class="text-end">発注点</th>
                    <th class="text-end">不足数</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($alertProducts as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <td>{{ $product->name }}</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($product->stock_quantity) }}</td>
                    <td class="text-end text-muted">{{ number_format($product->reorder_point) }}</td>
                    <td class="text-end fw-bold">{{ number_format($product->deficit) }}</td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">入庫</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
