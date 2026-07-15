<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '在庫管理') - StockManager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('products.index') }}">
            <i class="bi bi-boxes me-1"></i>StockManager
        </a>
        <div class="navbar-nav ms-auto d-flex flex-row gap-3 align-items-center">
            <a class="nav-link" href="{{ route('products.index') }}">
                <i class="bi bi-box-seam me-1"></i>商品一覧
            </a>
            <a class="nav-link" href="{{ route('stock-transactions.index') }}">
                <i class="bi bi-clock-history me-1"></i>履歴
            </a>
            <a class="nav-link" href="{{ route('products.reorder-list') }}">
                <i class="bi bi-clipboard2-check me-1"></i>発注リスト
            </a>
            <a class="nav-link position-relative" href="{{ route('products.index') }}?alert_only=1">
                <i class="bi bi-exclamation-triangle me-1"></i>アラート
                @php $alertCount = \App\Models\Product::belowReorderPoint()->count(); @endphp
                @if($alertCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $alertCount > 99 ? '99+' : $alertCount }}
                </span>
                @endif
            </a>
        </div>
    </div>
</nav>

<div class="container">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
