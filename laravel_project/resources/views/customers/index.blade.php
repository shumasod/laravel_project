@extends('layouts.app')

@section('title', '顧客一覧')

@push('styles')
<style>
    .search-filters {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .customer-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        flex: 1;
    }
    .stat-card h4 {
        margin: 0;
        font-size: 0.9rem;
        color: #666;
    }
    .stat-card .value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }
    #loadingIndicator {
        text-align: center;
        padding: 40px;
        display: none;
    }
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
    }
    .bg-success { background-color: #28a745; color: white; }
    .bg-secondary { background-color: #6c757d; color: white; }
    .bg-warning { background-color: #ffc107; color: #333; }
    .bg-danger { background-color: #dc3545; color: white; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>顧客一覧</h2>
    <div>
        <button type="button" class="btn btn-outline-primary me-2" onclick="window.customerApp?.getManager()?.refresh()">
            <i class="bi bi-arrow-clockwise"></i> 更新
        </button>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> 新規登録
        </a>
    </div>
</div>

<!-- 統計情報 -->
<div class="customer-stats">
    <div class="stat-card">
        <h4>総顧客数</h4>
        <div class="value">{{ $customers->total() }}</div>
    </div>
    <div class="stat-card">
        <h4>今月の新規</h4>
        <div class="value">{{ $customers->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
    </div>
    <div class="stat-card">
        <h4>有効顧客</h4>
        <div class="value">{{ $customers->where('status', 'active')->count() ?: $customers->count() }}</div>
    </div>
</div>

<!-- 検索・フィルター -->
<div class="search-filters">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">キーワード検索</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control"
                       placeholder="名前、メールアドレスで検索...">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">ステータス</label>
            <select id="statusFilter" class="form-select">
                <option value="">すべて</option>
                <option value="active">有効</option>
                <option value="inactive">無効</option>
                <option value="pending">保留中</option>
                <option value="suspended">停止</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                <i class="bi bi-x-circle"></i> フィルターをクリア
            </button>
        </div>
    </div>
</div>

<!-- ローディング表示 -->
<div id="loadingIndicator">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">読み込み中...</span>
    </div>
    <p class="mt-2 text-muted">顧客データを読み込んでいます...</p>
</div>

<!-- 顧客リスト -->
<div class="card">
    <div id="customerList">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>氏名</th>
                    <th>メールアドレス</th>
                    <th>電話番号</th>
                    <th>ステータス</th>
                    <th>予約数</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr data-customer-id="{{ $customer->id }}">
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>
                        @php
                            $status = $customer->status ?? 'active';
                            $statusColors = [
                                'active' => 'success',
                                'inactive' => 'secondary',
                                'pending' => 'warning',
                                'suspended' => 'danger'
                            ];
                            $statusLabels = [
                                'active' => '有効',
                                'inactive' => '無効',
                                'pending' => '保留中',
                                'suspended' => '停止'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                            {{ $statusLabels[$status] ?? $status }}
                        </span>
                    </td>
                    <td>{{ $customer->reservations->count() }}</td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-info">詳細</a>
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning">編集</a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">顧客が登録されていません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin: 1rem; display: flex; justify-content: center;">
        {{ $customers->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/customer-manager.js') }}"></script>
<script>
    // グローバル変数として顧客アプリを保持
    window.customerApp = null;

    // フィルタークリア関数
    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';

        // CustomerManagerが初期化されている場合は検索を実行
        if (window.customerApp?.getManager()) {
            window.customerApp.getManager().searchCustomers();
        }
    }

    // ページ読み込み時の初期化
    document.addEventListener('DOMContentLoaded', function() {
        // 既存のテーブルデータをJavaScriptで管理するための初期化
        // （サーバーサイドレンダリングとJavaScript両方で動作）
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const customerRows = document.querySelectorAll('#customerList tbody tr[data-customer-id]');

        // クライアントサイドフィルタリング（APIなしでも動作）
        function filterCustomers() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;

            customerRows.forEach(row => {
                const name = row.cells[1]?.textContent.toLowerCase() || '';
                const email = row.cells[2]?.textContent.toLowerCase() || '';
                const status = row.querySelector('.badge')?.textContent.trim() || '';

                const statusMap = {
                    '有効': 'active',
                    '無効': 'inactive',
                    '保留中': 'pending',
                    '停止': 'suspended'
                };
                const rowStatus = Object.entries(statusMap).find(([label]) => status.includes(label))?.[1] || '';

                const matchesSearch = !searchTerm ||
                    name.includes(searchTerm) ||
                    email.includes(searchTerm);
                const matchesStatus = !statusValue || rowStatus === statusValue;

                row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }

        // イベントリスナー設定
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(filterCustomers, 300);
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', filterCustomers);
        }
    });
</script>
@endpush
