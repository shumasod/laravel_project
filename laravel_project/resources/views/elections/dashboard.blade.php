@extends('layouts.app')

@section('title', '選挙分析ダッシュボード')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">選挙分析ダッシュボード</h1>
            <p class="text-muted">{{ $summary['period'] }} の選挙データ分析</p>
        </div>
    </div>

    <!-- サマリーカード -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">衆議院選挙</h5>
                    <h2>{{ $summary['total_hr_elections'] }}回</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">参議院選挙</h5>
                    <h2>{{ $summary['total_hc_elections'] }}回</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">登録政党数</h5>
                    <h2>{{ $summary['total_parties'] }}党</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">分析期間</h5>
                    <h2>{{ $toYear - $fromYear + 1 }}年間</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- 期間フィルター -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('elections.dashboard') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">開始年</label>
                            <select name="from_year" class="form-select">
                                @for ($year = 2010; $year <= 2026; $year++)
                                    <option value="{{ $year }}" {{ $fromYear == $year ? 'selected' : '' }}>
                                        {{ $year }}年
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">終了年</label>
                            <select name="to_year" class="form-select">
                                @for ($year = 2010; $year <= 2026; $year++)
                                    <option value="{{ $year }}" {{ $toYear == $year ? 'selected' : '' }}>
                                        {{ $year }}年
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">フィルター適用</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 衆議院選挙一覧 -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">衆議院議員総選挙</h5>
                </div>
                <div class="card-body">
                    @if($hrElections->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>選挙名</th>
                                        <th>投票日</th>
                                        <th>総議席</th>
                                        <th>投票率</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hrElections as $election)
                                        <tr>
                                            <td>{{ $election->name }}</td>
                                            <td>{{ $election->election_date->format('Y/m/d') }}</td>
                                            <td>{{ $election->total_seats }}議席</td>
                                            <td>{{ $election->voter_turnout ?? '-' }}%</td>
                                            <td>
                                                <a href="{{ route('elections.show', $election) }}"
                                                   class="btn btn-sm btn-outline-primary">詳細</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">データがありません</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- 参議院選挙一覧 -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">参議院議員通常選挙</h5>
                </div>
                <div class="card-body">
                    @if($hcElections->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>選挙名</th>
                                        <th>投票日</th>
                                        <th>改選議席</th>
                                        <th>投票率</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hcElections as $election)
                                        <tr>
                                            <td>{{ $election->name }}</td>
                                            <td>{{ $election->election_date->format('Y/m/d') }}</td>
                                            <td>{{ $election->total_seats }}議席</td>
                                            <td>{{ $election->voter_turnout ?? '-' }}%</td>
                                            <td>
                                                <a href="{{ route('elections.show', $election) }}"
                                                   class="btn btn-sm btn-outline-success">詳細</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">データがありません</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 政党一覧 -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">登録政党一覧</h5>
                </div>
                <div class="card-body">
                    @if($parties->count() > 0)
                        <div class="row">
                            @foreach($parties as $party)
                                <div class="col-md-3 mb-3">
                                    <div class="card h-100"
                                         style="border-left: 4px solid {{ $party->color ?? '#6c757d' }}">
                                        <div class="card-body py-2">
                                            <h6 class="card-title mb-1">{{ $party->name }}</h6>
                                            @if($party->short_name)
                                                <small class="text-muted">{{ $party->short_name }}</small>
                                            @endif
                                            <div class="mt-2">
                                                <a href="{{ route('parties.trend', $party) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    トレンド分析
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">政党が登録されていません</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 最新の世論調査 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">最新の世論調査データ</h5>
                    <a href="{{ route('poll-data.index') }}" class="btn btn-sm btn-primary">すべて見る</a>
                </div>
                <div class="card-body">
                    @if(count($latestPolls) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>政党</th>
                                        <th>支持率</th>
                                        <th>調査元</th>
                                        <th>調査日</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestPolls as $partyName => $polls)
                                        @php $latestPoll = $polls->first(); @endphp
                                        <tr>
                                            <td>
                                                <span class="badge"
                                                      style="background-color: {{ $latestPoll->party->color ?? '#6c757d' }}">
                                                    {{ $partyName }}
                                                </span>
                                            </td>
                                            <td><strong>{{ $latestPoll->support_rate }}%</strong></td>
                                            <td>{{ $latestPoll->source }}</td>
                                            <td>{{ $latestPoll->survey_end_date->format('Y/m/d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">世論調査データがありません</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- クイックアクション -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">クイックアクション</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#addElectionModal">
                                選挙を登録
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#addPartyModal">
                                政党を登録
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#addPollModal">
                                世論調査を登録
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#importModal">
                                CSVインポート
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 選挙登録モーダル -->
<div class="modal fade" id="addElectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('elections.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">選挙を登録</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">選挙名</label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="例: 第50回衆議院議員総選挙">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">種別</label>
                        <select name="type" class="form-select" required>
                            <option value="house_of_representatives">衆議院</option>
                            <option value="house_of_councillors">参議院</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">投票日</label>
                        <input type="date" name="election_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">総議席数</label>
                        <input type="number" name="total_seats" class="form-control" required
                               placeholder="例: 465">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">小選挙区</label>
                            <input type="number" name="single_seat_districts" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">比例代表</label>
                            <input type="number" name="proportional_seats" class="form-control">
                        </div>
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

<!-- 政党登録モーダル -->
<div class="modal fade" id="addPartyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('parties.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">政党を登録</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">政党名</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">略称</label>
                        <input type="text" name="short_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">シンボルカラー</label>
                        <input type="color" name="color" class="form-control form-control-color" value="#000000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">設立日</label>
                        <input type="date" name="founded_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-success">登録</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 世論調査登録モーダル -->
<div class="modal fade" id="addPollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('poll-data.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">世論調査データを登録</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">政党</label>
                        <select name="party_id" class="form-select" required>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}">{{ $party->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">調査元</label>
                        <select name="source" class="form-select" required>
                            <option value="NHK">NHK</option>
                            <option value="読売新聞">読売新聞</option>
                            <option value="朝日新聞">朝日新聞</option>
                            <option value="毎日新聞">毎日新聞</option>
                            <option value="日本経済新聞">日本経済新聞</option>
                            <option value="産経新聞">産経新聞</option>
                            <option value="共同通信">共同通信</option>
                            <option value="時事通信">時事通信</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">調査方法</label>
                        <select name="poll_type" class="form-select" required>
                            <option value="phone">電話調査</option>
                            <option value="online">オンライン調査</option>
                            <option value="exit_poll">出口調査</option>
                            <option value="mixed">混合調査</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">調査開始日</label>
                            <input type="date" name="survey_start_date" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">調査終了日</label>
                            <input type="date" name="survey_end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">支持率 (%)</label>
                        <input type="number" name="support_rate" class="form-control"
                               step="0.1" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">サンプルサイズ</label>
                        <input type="number" name="sample_size" class="form-control" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-info">登録</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSVインポートモーダル -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('elections.import-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">CSVインポート</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">データ種別</label>
                        <select name="type" class="form-select" required id="importType">
                            <option value="election">選挙結果データ</option>
                            <option value="poll">世論調査データ</option>
                        </select>
                    </div>
                    <div class="mb-3" id="electionTypeDiv">
                        <label class="form-label">選挙種別</label>
                        <select name="election_type" class="form-select">
                            <option value="house_of_representatives">衆議院</option>
                            <option value="house_of_councillors">参議院</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CSVファイル</label>
                        <input type="file" name="file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>選挙結果CSV形式:</strong><br>
                            election_name, election_date, district_name, district_type, party_name, votes, seats_won, is_winner
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-warning">インポート</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('importType').addEventListener('change', function() {
    document.getElementById('electionTypeDiv').style.display =
        this.value === 'election' ? 'block' : 'none';
});
</script>
@endpush
@endsection
