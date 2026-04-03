@extends('layouts.app')

@section('title', $product->name . ' - 商品詳細')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-box-seam me-2"></i>{{ $product->name }}</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>編集
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>一覧へ
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- 左カラム: 商品情報 + QRコード --}}
    <div class="col-md-4">
        {{-- 商品情報 --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-info-circle me-1"></i>商品情報
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">SKU</dt>
                    <dd class="col-7"><code>{{ $product->sku }}</code></dd>

                    <dt class="col-5 text-muted">商品名</dt>
                    <dd class="col-7">{{ $product->name }}</dd>

                    <dt class="col-5 text-muted">在庫数</dt>
                    <dd class="col-7">
                        <span class="fs-5 fw-bold {{ $product->isOutOfStock() ? 'text-danger' : 'text-success' }}">
                            {{ number_format($product->stock_quantity) }}
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">発注点</dt>
                    <dd class="col-7">{{ number_format($product->reorder_point) }}</dd>

                    <dt class="col-5 text-muted">ステータス</dt>
                    <dd class="col-7">
                        @if($product->isOutOfStock())
                            <span class="badge bg-danger">在庫切れ</span>
                        @elseif($product->isBelowReorderPoint())
                            <span class="badge bg-warning text-dark">要発注</span>
                        @else
                            <span class="badge bg-success">正常</span>
                        @endif
                    </dd>

                    @if($product->description)
                    <dt class="col-5 text-muted">説明</dt>
                    <dd class="col-7">{{ $product->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- QRコード --}}
        <div class="card" id="qrcode">
            <div class="card-header fw-semibold">
                <i class="bi bi-qr-code me-1"></i>QRコード
            </div>
            <div class="card-body text-center">
                <p class="text-muted small mb-3">
                    スキャンすると商品詳細ページへアクセスできます
                </p>
                <div class="d-inline-block border rounded p-2 bg-white">
                    <img src="{{ route('products.qrcode', $product) }}"
                         alt="QRコード - {{ $product->sku }}"
                         width="200" height="200">
                </div>
                <p class="text-muted small mt-2 mb-3">
                    <code>{{ $product->sku }}</code>
                </p>
                <a href="{{ route('products.qrcode.download', $product) }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i>PNGダウンロード
                </a>
            </div>
        </div>
    </div>

    {{-- 右カラム: 在庫操作 + 履歴 --}}
    <div class="col-md-8">
        {{-- 在庫操作フォーム --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-arrow-left-right me-1"></i>在庫操作
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('stock-transactions.store', $product) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">操作種別</label>
                            <select name="type" class="form-select" required>
                                <option value="IN" @selected(old('type')==='IN')>入庫</option>
                                <option value="OUT" @selected(old('type')==='OUT')>出庫</option>
                                <option value="ADJUST" @selected(old('type')==='ADJUST')>在庫調整</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">数量</label>
                            <input type="number" name="quantity" class="form-control"
                                   min="1" value="{{ old('quantity', 1) }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">理由・メモ</label>
                            <input type="text" name="reason" class="form-control"
                                   placeholder="任意" value="{{ old('reason') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i>実行
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- 在庫履歴 --}}
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-clock-history me-1"></i>在庫履歴
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>日時</th>
                            <th>種別</th>
                            <th class="text-end">数量</th>
                            <th>理由</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td class="text-muted small">{{ $tx->created_at->format('Y/m/d H:i') }}</td>
                            <td>
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
                            <td colspan="4" class="text-center text-muted py-3">履歴がありません</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
