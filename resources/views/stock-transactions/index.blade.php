@extends('layouts.app')

@section('title', '在庫履歴')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-clock-history me-2"></i>在庫履歴</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('stock-transactions.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSVエクスポート
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-box-seam me-1"></i>商品一覧
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('stock-transactions.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">種別</label>
                <select name="type" class="form-select">
                    <option value="">すべて</option>
                    <option value="IN" @selected(request('type')==='IN')>入庫</option>
                    <option value="OUT" @selected(request('type')==='OUT')>出庫</option>
                    <option value="ADJUST" @selected(request('type')==='ADJUST')>調整</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">絞り込み</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('stock-transactions.index') }}" class="btn btn-secondary w-100">クリア</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>日時</th>
                    <th>商品</th>
                    <th>SKU</th>
                    <th class="text-center">種別</th>
                    <th class="text-end">数量</th>
                    <th>理由</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td class="text-muted small">{{ $tx->created_at->format('Y/m/d H:i') }}</td>
                    <td>
                        <a href="{{ route('products.show', $tx->product) }}" class="text-decoration-none">
                            {{ $tx->product->name }}
                        </a>
                    </td>
                    <td><code>{{ $tx->product->sku }}</code></td>
                    <td class="text-center">
                        @if($tx->type->value === 'IN')
                            <span class="badge bg-success">入庫</span>
                        @elseif($tx->type->value === 'OUT')
                            <span class="badge bg-danger">出庫</span>
                        @else
                            <span class="badge bg-secondary">調整</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($tx->quantity) }}</td>
                    <td class="text-muted small">{{ $tx->reason ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">履歴がありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $transactions->withQueryString()->links() }}
</div>
@endsection
