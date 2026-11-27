@extends('layouts.app')

@section('title', '商品別入出庫サマリー')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-bar-chart me-2"></i>商品別入出庫サマリー</h2>
    <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-clock-history me-1"></i>履歴一覧
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>商品名</th>
                    <th class="text-end text-success">入庫計</th>
                    <th class="text-end text-danger">出庫計</th>
                    <th class="text-end text-secondary">調整計</th>
                    <th class="text-end">現在庫</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $row)
                <tr>
                    <td><code>{{ $row['product']?->sku }}</code></td>
                    <td>
                        <a href="{{ route('products.show', $row['product']) }}" class="text-decoration-none">
                            {{ $row['product']?->name }}
                        </a>
                    </td>
                    <td class="text-end text-success fw-semibold">+{{ number_format($row['IN']) }}</td>
                    <td class="text-end text-danger fw-semibold">-{{ number_format($row['OUT']) }}</td>
                    <td class="text-end text-secondary">{{ number_format($row['ADJUST']) }}</td>
                    <td class="text-end fw-bold">{{ number_format($row['product']?->stock_quantity ?? 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">データがありません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
