@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <div>
        <h2 class="mb-0">{{ $product->name }}</h2>
        <code class="text-muted">{{ $product->sku }}</code>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.qrcode.download', $product) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i>PNGダウンロード
        </a>
        <a href="{{ route('products.qrcode.download.svg', $product) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i>SVGダウンロード
        </a>
        <a href="{{ route('products.label', $product) }}" class="btn btn-outline-dark" target="_blank">
            <i class="bi bi-printer me-1"></i>ラベル印刷
        </a>
        <form method="POST" action="{{ route('products.duplicate', $product) }}"
              onsubmit="return confirm('この商品を複製しますか？')">
            @csrf
            <button type="submit" class="btn btn-outline-info">
                <i class="bi bi-copy me-1"></i>複製
            </button>
        </form>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>編集
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="{{ route('products.qrcode', $product) }}" alt="QR Code" class="img-fluid mb-2" style="max-width:200px;">
                <p class="text-muted small mb-0">商品詳細ページへのQRコード</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">在庫情報</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6">現在庫数</dt>
                    <dd class="col-6 fw-bold fs-5">{{ number_format($product->stock_quantity) }}</dd>
                    <dt class="col-6">発注点</dt>
                    <dd class="col-6">{{ number_format($product->reorder_point) }}</dd>
                    @if($product->description)
                    <dt class="col-6">説明</dt>
                    <dd class="col-6">{{ $product->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">在庫操作</div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif
                <form method="POST" action="{{ route('stock-transactions.store', $product) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">操作種別</label>
                            <select name="type" class="form-select" required>
                                <option value="IN">入庫</option>
                                <option value="OUT">出庫</option>
                                <option value="ADJUST">調整</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">数量</label>
                            <input type="number" name="quantity" class="form-control" min="1" required value="{{ old('quantity') }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">理由</label>
                            <input type="text" name="reason" id="reason" class="form-control" placeholder="任意" value="{{ old('reason') }}">
                            <div class="mt-1 d-flex flex-wrap gap-1">
                                @foreach(['仕入れ', '返品入庫', '販売', '廃棄', '棚卸調整', '移動'] as $preset)
                                <button type="button" class="btn btn-sm btn-outline-secondary reason-preset"
                                    data-reason="{{ $preset }}">{{ $preset }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">実行</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">在庫履歴（直近50件）</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>日時</th>
                            <th class="text-center">種別</th>
                            <th class="text-end">数量</th>
                            <th>理由</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td class="text-muted small">{{ $tx->created_at->format('Y/m/d H:i') }}</td>
                            <td class="text-center">
                                @if($tx->type->value === 'IN')
                                    <span class="badge bg-success">入庫</span>
                                @elseif($tx->type->value === 'OUT')
                                    <span class="badge bg-danger">出庫</span>
                                @else
                                    <span class="badge bg-secondary">調整</span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($tx->quantity) }}</td>
                            <td class="text-muted small">{{ $tx->reason ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">履歴がありません</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.reason-preset').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('reason').value = btn.dataset.reason;
    });
});
</script>
@endpush
