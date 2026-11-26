@extends('layouts.app')
@section('title', '一括入庫')
@section('content')
<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-box-arrow-in-down me-2"></i>一括入庫</h2>
        <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>履歴一覧
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted small mb-3">入庫数量を入力した行だけ登録されます。数量が空または0の行はスキップされます。</p>
            <form method="POST" action="{{ route('stock-transactions.bulk.store') }}">
                @csrf
                <table class="table table-sm table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>SKU</th>
                            <th>商品名</th>
                            <th class="text-end" style="width:12%">現在在庫</th>
                            <th style="width:15%">入庫数量</th>
                            <th>理由・メモ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $i => $product)
                        <tr>
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td><code>{{ $product->sku }}</code>
                                <input type="hidden" name="entries[{{ $i }}][product_id]" value="{{ $product->id }}">
                            </td>
                            <td>{{ $product->name }}</td>
                            <td class="text-end fw-semibold">{{ number_format($product->stock_quantity) }}</td>
                            <td>
                                <input type="number" name="entries[{{ $i }}][quantity]"
                                    class="form-control form-control-sm" min="0" placeholder="0">
                            </td>
                            <td>
                                <input type="text" name="entries[{{ $i }}][reason]"
                                    class="form-control form-control-sm" placeholder="任意">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-all me-1"></i>一括入庫を登録
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
