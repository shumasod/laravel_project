@extends('layouts.app')

@section('title', $election->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('elections.dashboard') }}">選挙分析</a></li>
                    <li class="breadcrumb-item active">{{ $election->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">{{ $election->name }}</h1>
            <p class="text-muted">{{ $election->election_date->format('Y年m月d日') }} 投票</p>
        </div>
    </div>

    <!-- 選挙情報 -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">選挙概要</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">選挙種別</label>
                            <p class="mb-0 fw-bold">{{ $election->type_name }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">投票日</label>
                            <p class="mb-0 fw-bold">{{ $election->election_date->format('Y/m/d') }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">総議席数</label>
                            <p class="mb-0 fw-bold">{{ $election->total_seats }}議席</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">投票率</label>
                            <p class="mb-0 fw-bold">{{ $election->voter_turnout ?? '-' }}%</p>
                        </div>
                        @if($election->single_seat_districts)
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">小選挙区</label>
                            <p class="mb-0 fw-bold">{{ $election->single_seat_districts }}議席</p>
                        </div>
                        @endif
                        @if($election->proportional_seats)
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small">比例代表</label>
                            <p class="mb-0 fw-bold">{{ $election->proportional_seats }}議席</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">アクション</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <button class="btn btn-primary mb-2" id="runPrediction">
                        議席予測を実行
                    </button>
                    <a href="{{ route('elections.export', ['election' => $election, 'format' => 'csv']) }}"
                       class="btn btn-outline-secondary mb-2">
                        CSVエクスポート
                    </a>
                    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#addResultModal">
                        選挙結果を登録
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 選挙結果 -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">選挙結果（政党別）</h5>
                </div>
                <div class="card-body">
                    @if($resultsByParty->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>政党</th>
                                        <th class="text-end">総議席</th>
                                        <th class="text-end">小選挙区</th>
                                        <th class="text-end">比例</th>
                                        <th class="text-end">得票数</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resultsByParty as $result)
                                        <tr>
                                            <td>
                                                <span class="badge me-1"
                                                      style="background-color: {{ $result['party_color'] ?? '#6c757d' }}">
                                                    &nbsp;
                                                </span>
                                                {{ $result['party_name'] }}
                                            </td>
                                            <td class="text-end fw-bold">{{ $result['total_seats'] }}</td>
                                            <td class="text-end">{{ $result['single_seat_wins'] }}</td>
                                            <td class="text-end">{{ $result['proportional_seats'] }}</td>
                                            <td class="text-end">{{ number_format($result['total_votes']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- 議席分布チャート -->
                        <div class="mt-3">
                            <h6>議席分布</h6>
                            <div class="progress" style="height: 30px;">
                                @php $offset = 0; @endphp
                                @foreach($resultsByParty as $result)
                                    @php
                                        $percentage = $election->total_seats > 0
                                            ? ($result['total_seats'] / $election->total_seats * 100)
                                            : 0;
                                    @endphp
                                    @if($percentage > 0)
                                        <div class="progress-bar"
                                             style="width: {{ $percentage }}%; background-color: {{ $result['party_color'] ?? '#6c757d' }}"
                                             title="{{ $result['party_name'] }}: {{ $result['total_seats'] }}議席">
                                            @if($percentage > 5)
                                                {{ $result['total_seats'] }}
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-muted">選挙結果データがありません</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- 議席予測 -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">議席予測</h5>
                    <span class="badge bg-secondary" id="predictionStatus">
                        {{ $latestPredictions->count() > 0 ? '予測済み' : '未予測' }}
                    </span>
                </div>
                <div class="card-body" id="predictionResults">
                    @if($latestPredictions->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>政党</th>
                                        <th class="text-end">予測議席</th>
                                        <th class="text-end">範囲</th>
                                        <th class="text-end">信頼度</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestPredictions->sortByDesc('predicted_seats') as $prediction)
                                        <tr>
                                            <td>{{ $prediction->party->name }}</td>
                                            <td class="text-end fw-bold">{{ $prediction->predicted_seats }}</td>
                                            <td class="text-end text-muted">
                                                {{ $prediction->min_seats }} - {{ $prediction->max_seats }}
                                            </td>
                                            <td class="text-end">
                                                <span class="badge {{ $prediction->confidence_level >= 80 ? 'bg-success' : ($prediction->confidence_level >= 60 ? 'bg-warning' : 'bg-secondary') }}">
                                                    {{ round($prediction->confidence_level) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">
                            最終更新: {{ $latestPredictions->first()->predicted_at->format('Y/m/d H:i') }}
                        </small>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-3">予測データがありません</p>
                            <button class="btn btn-primary" id="runPredictionEmpty">
                                議席予測を実行
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 予測精度検証 -->
    @if($resultsByParty->count() > 0 && $latestPredictions->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">予測精度検証</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-outline-primary" id="validateAccuracy">
                        精度を検証
                    </button>
                    <div id="accuracyResults" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- 選挙結果登録モーダル -->
<div class="modal fade" id="addResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('elections.results.store', $election) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">選挙結果を登録</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">選挙区</label>
                        <select name="district_id" class="form-select" required>
                            <option value="">選択してください</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">政党</label>
                        <select name="party_id" class="form-select" required>
                            <option value="">選択してください</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">候補者名（小選挙区の場合）</label>
                        <input type="text" name="candidate_name" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">得票数</label>
                            <input type="number" name="votes" class="form-control" required min="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">獲得議席</label>
                            <input type="number" name="seats_won" class="form-control" required min="0">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_winner" class="form-check-input" id="isWinner" value="1">
                        <label class="form-check-label" for="isWinner">当選</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">登録</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 議席予測実行
document.querySelectorAll('#runPrediction, #runPredictionEmpty').forEach(btn => {
    btn.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 予測中...';

        fetch('{{ route("elections.predict", $election) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('エラー: ' + data.message);
                this.disabled = false;
                this.innerHTML = '議席予測を実行';
            }
        })
        .catch(error => {
            alert('エラーが発生しました');
            this.disabled = false;
            this.innerHTML = '議席予測を実行';
        });
    });
});

// 精度検証
document.getElementById('validateAccuracy')?.addEventListener('click', function() {
    this.disabled = true;

    fetch('{{ route("elections.validate-accuracy", $election) }}')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const container = document.getElementById('accuracyResults');
            container.innerHTML = '';

            const table = document.createElement('table');
            table.className = 'table table-sm';
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            ['政党', '予測', '実績', '誤差', '範囲内'].forEach(text => {
                const th = document.createElement('th');
                th.textContent = text;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            for (const [party, result] of Object.entries(data.data.validation)) {
                const tr = document.createElement('tr');
                [party, result.predicted, result.actual, result.error].forEach(val => {
                    const td = document.createElement('td');
                    td.textContent = val;
                    tr.appendChild(td);
                });
                const tdBadge = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = result.within_range ? 'badge bg-success' : 'badge bg-danger';
                badge.textContent = result.within_range ? 'Yes' : 'No';
                tdBadge.appendChild(badge);
                tr.appendChild(tdBadge);
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);
            container.appendChild(table);

            const note = document.createElement('p');
            note.className = 'text-muted';
            note.textContent = `平均誤差: ${data.data.average_error}議席`;
            container.appendChild(note);
        }
        this.disabled = false;
    });
});

// モーダルのデータ取得
document.getElementById('addResultModal')?.addEventListener('show.bs.modal', function() {
    // 選挙区取得
    fetch('{{ route("districts.index") }}?house_type={{ $election->type }}')
    .then(response => response.json())
    .then(data => {
        const select = this.querySelector('select[name="district_id"]');
        select.innerHTML = '<option value="">選択してください</option>';
        data.data.forEach(district => {
            const opt = document.createElement('option');
            opt.value = district.id;
            opt.textContent = district.name;
            select.appendChild(opt);
        });
    });

    // 政党取得
    fetch('{{ route("parties.index") }}')
    .then(response => response.json())
    .then(data => {
        const select = this.querySelector('select[name="party_id"]');
        select.innerHTML = '<option value="">選択してください</option>';
        data.data.forEach(party => {
            const opt = document.createElement('option');
            opt.value = party.id;
            opt.textContent = party.name;
            select.appendChild(opt);
        });
    });
});
</script>
@endpush
@endsection
