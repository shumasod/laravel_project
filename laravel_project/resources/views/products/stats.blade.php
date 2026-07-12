@extends('layouts.app')

@section('title', '在庫統計ダッシュボード')

@section('content')
<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart-line me-2"></i>在庫統計ダッシュボード</h2>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>商品一覧へ
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center p-3 border-0 shadow-sm">
                <div class="fs-1 fw-bold text-primary">{{ $stats['total'] }}</div>
                <div class="text-muted small">総商品数</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center p-3 border-0 shadow-sm">
                <div class="fs-1 fw-bold text-success">{{ $stats['normal'] }}</div>
                <div class="text-muted small">正常在庫</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center p-3 border-0 shadow-sm">
                <div class="fs-1 fw-bold text-warning">{{ $stats['below_reorder'] }}</div>
                <div class="text-muted small">要発注</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center p-3 border-0 shadow-sm">
                <div class="fs-1 fw-bold text-danger">{{ $stats['out_of_stock'] }}</div>
                <div class="text-muted small">在庫切れ</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light fw-semibold">
            <i class="bi bi-clock-history me-1"></i>直近10件の在庫操作
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>日時</th><th>商品</th><th>操作</th><th class="text-end">数量</th><th>理由</th></tr>
                </thead>
                <tbody>
                    @forelse($recent as $tx)
                    <tr>
                        <td class="small text-muted">{{ $tx->created_at->format('m/d H:i') }}</td>
                        <td><a href="{{ route('products.show', $tx->product) }}">{{ $tx->product->name }}</a></td>
                        <td>
                            @if($tx->type->value === 'in')<span class="badge bg-success">入庫</span>
                            @elseif($tx->type->value === 'out')<span class="badge bg-danger">出庫</span>
                            @else<span class="badge bg-secondary">調整</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">{{ $tx->quantity }}</td>
                        <td class="small text-muted">{{ $tx->reason ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">操作履歴がありません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
