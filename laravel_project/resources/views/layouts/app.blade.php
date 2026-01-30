<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '宿泊管理システム')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .navbar-dark .navbar-nav .nav-link {
            color: rgba(255,255,255,0.85);
        }
        .navbar-dark .navbar-nav .nav-link:hover {
            color: #fff;
        }
        .navbar-dark .navbar-nav .nav-link.active {
            color: #fff;
            font-weight: 600;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .dropdown-item:hover {
            background-color: #3498db;
            color: white;
        }
        .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }
        nav {
            background-color: #2c3e50;
            padding: 0;
            margin-bottom: 0;
        }
        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav h1 {
            color: white;
            font-size: 1.5rem;
        }
        nav ul {
            display: flex;
            list-style: none;
            gap: 1.5rem;
        }
        nav a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #3498db;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-success {
            background-color: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background-color: #229954;
        }
        table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #34495e;
            color: white;
        }
        .card {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2c3e50;">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-building me-2"></i>総合管理システム
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- 旅行検索 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('travel*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-airplane me-1"></i>旅行検索
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('travel.index') }}"><i class="bi bi-house-door"></i>トップページ</a></li>
                            <li><a class="dropdown-item" href="{{ route('travel.search') }}"><i class="bi bi-search"></i>宿泊施設検索</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('travel.search', ['type' => 'hotel']) }}"><i class="bi bi-building"></i>ホテル</a></li>
                            <li><a class="dropdown-item" href="{{ route('travel.search', ['type' => 'ryokan']) }}"><i class="bi bi-house"></i>旅館</a></li>
                            <li><a class="dropdown-item" href="{{ route('travel.search', ['type' => 'pension']) }}"><i class="bi bi-tree"></i>ペンション</a></li>
                        </ul>
                    </li>

                    <!-- 選挙分析 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('elections*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bar-chart me-1"></i>選挙分析
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('elections.dashboard') }}"><i class="bi bi-speedometer2"></i>ダッシュボード</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('elections.dashboard', ['type' => 'hr']) }}"><i class="bi bi-bank"></i>衆議院選挙</a></li>
                            <li><a class="dropdown-item" href="{{ route('elections.dashboard', ['type' => 'hc']) }}"><i class="bi bi-bank2"></i>参議院選挙</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('elections.index') }}"><i class="bi bi-people"></i>政党一覧</a></li>
                        </ul>
                    </li>

                    <!-- 宿泊管理 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('accommodations*') || request()->is('rooms*') || request()->is('reservations*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar-check me-1"></i>宿泊管理
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('accommodations.index') }}"><i class="bi bi-building"></i>宿泊施設</a></li>
                            <li><a class="dropdown-item" href="{{ route('rooms.index') }}"><i class="bi bi-door-open"></i>部屋</a></li>
                            <li><a class="dropdown-item" href="{{ route('reservations.index') }}"><i class="bi bi-calendar3"></i>予約</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('customers.index') }}"><i class="bi bi-person"></i>顧客</a></li>
                            <li><a class="dropdown-item" href="{{ route('reviews.index') }}"><i class="bi bi-star"></i>レビュー</a></li>
                        </ul>
                    </li>

                    <!-- 経理・レポート -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('payments*') || request()->is('reports*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-graph-up me-1"></i>経理・レポート
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('payments.index') }}"><i class="bi bi-credit-card"></i>決済</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports.dashboard') }}"><i class="bi bi-clipboard-data"></i>レポート</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
