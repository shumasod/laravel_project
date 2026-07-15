@extends('layouts.app')

@section('title', '商品一覧')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-box-seam me-2"></i>商品一覧</h2>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>新規登録
    </a>
</div>

{{-- Stats widget --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-primary">{{ number_format($stats['total']) }}</div>
                <div class="text-muted small">登録商品数</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-center border-0 {{ $stats['alert_count'] > 0 ? 'bg-danger bg-opacity-10' : 'bg-light' }}">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold {{ $stats['alert_count'] > 0 ? 'text-danger' : 'text-secondary' }}">{{ number_format($stats['alert_count']) }}</div>
                <div class="text-muted small">在庫アラート</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-center border-0 bg-light">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-success">{{ number_format($stats['total_stock']) }}</div>
                <div class="text-muted small">総在庫数</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="search" name="search" class="form-control" placeholder="商品名・SKUで検索"
                    value="{{ request('search') }}" list="productSuggestions" autocomplete="off">
                <datalist id="productSuggestions"></datalist>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <option value="stock_asc" @selected(request('sort','stock_asc')==='stock_asc')>在庫少ない順</option>
                    <option value="stock_desc" @selected(request('sort')==='stock_desc')>在庫多い順</option>
                    <option value="name" @selected(request('sort')==='name')>名前順</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="alert_only" id="alertOnly" value="1"
                        @checked(request()->boolean('alert_only'))>
                    <label class="form-check-label" for="alertOnly">アラートのみ</label>
                </div>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">検索</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('products.index') }}" class="btn btn-secondary w-100">クリア</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>商品名</th>
                    <th class="text-end">在庫数</th>
                    <th class="text-end">発注点</th>
                    <th class="text-center">状態</th>
                    <th class="text-center">クイック調整</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                    <td><code>{{ $product->sku }}</code></td>
                    <td>{{ $product->name }}</td>
                    <td class="text-end fw-semibold">
                        <span class="stock-display" data-id="{{ $product->id }}">{{ number_format($product->stock_quantity) }}</span>
                    </td>
                    <td class="text-end text-muted">{{ number_format($product->reorder_point) }}</td>
                    <td class="text-center">
                        @if($product->isLowStock())
                            <span class="badge bg-warning text-dark">要発注</span>
                        @else
                            <span class="badge bg-success">正常</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-success quick-adjust" data-url="{{ route('stock-transactions.quick-adjust', $product) }}" data-delta="1">＋</button>
                        <button class="btn btn-sm btn-outline-danger quick-adjust" data-url="{{ route('stock-transactions.quick-adjust', $product) }}" data-delta="-1">－</button>
                    </td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">商品が見つかりません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $products->withQueryString()->links() }}
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.quick-adjust').forEach(btn => {
    btn.addEventListener('click', async () => {
        const url = btn.dataset.url;
        const delta = btn.dataset.delta;
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ delta })
        });
        if (res.ok) {
            const data = await res.json();
            const row = btn.closest('tr');
            row.querySelector('.stock-display').textContent = data.stock.toLocaleString();
        }
    });
});

let suggestTimer;
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(suggestTimer);
        suggestTimer = setTimeout(async () => {
            const q = searchInput.value.trim();
            if (q.length < 1) return;
            const res = await fetch('{{ route("products.suggest") }}?q=' + encodeURIComponent(q));
            if (!res.ok) return;
            const items = await res.json();
            const dl = document.getElementById('productSuggestions');
            dl.innerHTML = items.map(p => `<option value="${p.name}">${p.sku}</option>`).join('');
        }, 200);
    });
}
</script>
@endpush
