@extends('layouts.app')

@section('title', 'CSVインポート')

@section('content')
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h2><i class="bi bi-file-earmark-arrow-up me-2"></i>商品CSVインポート</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>商品一覧に戻る
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">アップロード</div>
            <div class="card-body">
                <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">CSVファイル</label>
                        <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror" accept=".csv,.txt">
                        @error('csv_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>インポート実行
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">ファイル形式</div>
            <div class="card-body">
                <p class="text-muted small">ヘッダ行の次の行からデータとして読み込みます。SKUと商品名は必須です。</p>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>A列</th><th>B列</th><th>C列</th><th>D列</th><th>E列</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>SKU<span class="text-danger">*</span></td>
                            <td>商品名<span class="text-danger">*</span></td>
                            <td>説明</td>
                            <td>在庫数</td>
                            <td>発注点</td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-muted small mb-0">既存SKUはスキップされます。在庫数・発注点の省略時は0になります。</p>
            </div>
        </div>
    </div>
</div>
@endsection
