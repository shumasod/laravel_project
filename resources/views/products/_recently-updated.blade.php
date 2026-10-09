@if($recentlyUpdated->isNotEmpty())
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold small"><i class="bi bi-clock me-1"></i>最近更新された商品</span>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @foreach($recentlyUpdated as $p)
            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                <div>
                    <a href="{{ route('products.show', $p) }}" class="text-decoration-none fw-semibold">{{ $p->name }}</a>
                    <span class="text-muted small ms-2"><code>{{ $p->sku }}</code></span>
                </div>
                <div class="text-end">
                    <span class="fw-bold {{ $p->isLowStock() ? 'text-danger' : 'text-success' }}">{{ number_format($p->stock_quantity) }}</span>
                    <span class="text-muted small ms-2">{{ $p->updated_at->diffForHumans() }}</span>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endif
