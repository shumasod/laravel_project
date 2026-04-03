@extends('layouts.app')

@section('title', '商品一覧 - 在庫管理')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-box-seam me-2"></i>商品一覧</h2>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>商品登録
    </a>
</div>

{{-- 検索フォーム --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">検索</label>
                <input type="text" name="search" class="form-control" placeholder="SKU・商品名で検索"
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">並び替え</label>
                <select name="sort" class="form-select">
                    <option value="stock_asc" @selected(request('sort','stock_asc')==='stock_asc')>在庫が少ない順</option>
                    <option value="stock_desc" @selected(request('sort')==='stock_desc')>在庫が多い順</option>
                    <option value="name" @selected(request('sort')==='name')>商品名順</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="alert_only" value="1" id="alertOnly"
                        @checked(request('alert_only'))>
                    <label class="form-check-label" for="alertOnly">アラートのみ</label>
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 商品テーブル --}}
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>商品名</th>
                    <th class="text-end">在庫数</th>
                    <th class="text-end">発注点</th>
                    <th class="text-center">ステータス</th>
                    <th class="text-center">QRコード</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="{{ $product->isBelowReorderPoint() ? 'table-warning' : '' }}">
                    <td><code>{{ $product->sku }}</code></td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none fw-semibold">
                            {{ $product->name }}
                        </a>
                    </td>
                    <td class="text-end fw-bold {{ $product->isOutOfStock() ? 'text-danger' : '' }}">
                        {{ number_format($product->stock_quantity) }}
                    </td>
                    <td class="text-end text-muted">{{ number_format($product->reorder_point) }}</td>
                    <td class="text-center">
                        @if($product->isOutOfStock())
                            <span class="badge bg-danger">在庫切れ</span>
                        @elseif($product->isBelowReorderPoint())
                            <span class="badge bg-warning text-dark">要発注</span>
                        @else
                            <span class="badge bg-success">正常</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('products.show', $product) }}#qrcode"
                            class="btn btn-sm btn-outline-secondary" title="QRコードを表示">
                            <i class="bi bi-qr-code"></i>
                        </a>
                        <a href="{{ route('products.qrcode.download', $product) }}"
                            class="btn btn-sm btn-outline-secondary" title="QRコードをダウンロード">
                            <i class="bi bi-download"></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline"
                            onsubmit="return confirm('削除しますか？')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        商品が登録されていません
                    </td>
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
