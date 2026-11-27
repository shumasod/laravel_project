<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\ElectionDistrict;
use App\Models\ElectionResult;
use App\Models\PoliticalParty;
use App\Models\PollData;
use App\Models\SeatPrediction;
use App\Services\ElectionDataService;
use App\Services\ElectionAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ElectionController extends Controller
{
    public function __construct(
        private ElectionDataService $dataService,
        private ElectionAnalysisService $analysisService
    ) {}

    /**
     * 選挙分析ダッシュボード
     */
    public function dashboard(Request $request)
    {
        $request->validate([
            'from_year' => 'nullable|integer|min:1900|max:2100',
            'to_year'   => 'nullable|integer|min:1900|max:2100|gte:from_year',
        ]);

        $fromYear = (int) $request->input('from_year', 2010);
        $toYear   = (int) $request->input('to_year', 2026);

        // 衆議院選挙一覧
        $hrElections = Election::houseOfRepresentatives()
            ->betweenDates(
                Carbon::create($fromYear, 1, 1),
                Carbon::create($toYear, 12, 31)
            )
            ->orderBy('election_date', 'desc')
            ->get();

        // 参議院選挙一覧
        $hcElections = Election::houseOfCouncillors()
            ->betweenDates(
                Carbon::create($fromYear, 1, 1),
                Carbon::create($toYear, 12, 31)
            )
            ->orderBy('election_date', 'desc')
            ->get();

        // 最新の世論調査（各政党）
        $latestPolls = PollData::with('party')
            ->orderBy('survey_end_date', 'desc')
            ->take(20)
            ->get()
            ->groupBy('party.name');

        // 政党一覧
        $parties = PoliticalParty::active()->orderBy('name')->get();

        // 統計サマリー
        $summary = [
            'total_hr_elections' => $hrElections->count(),
            'total_hc_elections' => $hcElections->count(),
            'total_parties' => $parties->count(),
            'period' => "{$fromYear}年 - {$toYear}年",
        ];

        return view('elections.dashboard', compact(
            'hrElections',
            'hcElections',
            'latestPolls',
            'parties',
            'summary',
            'fromYear',
            'toYear'
        ));
    }

    /**
     * 選挙一覧（API）
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->input('type'); // house_of_representatives / house_of_councillors
        $fromYear = $request->input('from_year', 2010);
        $toYear = $request->input('to_year', 2026);

        $elections = $this->dataService->getElections($type, $fromYear, $toYear);

        return response()->json([
            'status' => 'success',
            'data' => $elections,
            'period' => [
                'from' => $fromYear,
                'to' => $toYear,
            ],
        ]);
    }

    /**
     * 選挙詳細
     */
    public function show(Election $election)
    {
        $election->load(['results.party', 'results.district', 'seatPredictions.party']);

        // 政党別集計
        $resultsByParty = $election->results
            ->groupBy('party.name')
            ->map(function ($results, $partyName) {
                $party = $results->first()->party;
                return [
                    'party_name' => $partyName,
                    'party_color' => $party->color,
                    'total_votes' => $results->sum('votes'),
                    'total_seats' => $results->sum('seats_won'),
                    'single_seat_wins' => $results->where('district.type', 'single_seat')->sum('seats_won'),
                    'proportional_seats' => $results->where('district.type', 'proportional')->sum('seats_won'),
                ];
            })
            ->sortByDesc('total_seats')
            ->values();

        // 最新の予測
        $latestPredictions = $election->seatPredictions
            ->groupBy('party_id')
            ->map(function ($predictions) {
                return $predictions->sortByDesc('predicted_at')->first();
            });

        return view('elections.show', compact('election', 'resultsByParty', 'latestPredictions'));
    }

    /**
     * 議席予測を実行
     */
    public function predict(Request $request, Election $election): JsonResponse
    {
        try {
            $result = $this->analysisService->predictSeats($election->id);

            return response()->json([
                'status' => 'success',
                'message' => '議席予測が完了しました',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Election seat prediction failed', ['election_id' => $election->id, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => '予測中にエラーが発生しました。',
            ], 500);
        }
    }

    /**
     * 予測結果の取得
     */
    public function getPredictions(Election $election): JsonResponse
    {
        $predictions = SeatPrediction::with('party')
            ->where('election_id', $election->id)
            ->orderBy('predicted_at', 'desc')
            ->get()
            ->groupBy('party.name')
            ->map(function ($predictions) {
                $latest = $predictions->first();
                return [
                    'party_name' => $latest->party->name,
                    'party_color' => $latest->party->color,
                    'predicted_seats' => $latest->predicted_seats,
                    'min_seats' => $latest->min_seats,
                    'max_seats' => $latest->max_seats,
                    'single_seat_prediction' => $latest->single_seat_prediction,
                    'proportional_prediction' => $latest->proportional_prediction,
                    'confidence_level' => $latest->confidence_level,
                    'predicted_at' => $latest->predicted_at->format('Y-m-d H:i'),
                ];
            })
            ->sortByDesc('predicted_seats')
            ->values();

        return response()->json([
            'status' => 'success',
            'election' => [
                'id' => $election->id,
                'name' => $election->name,
                'date' => $election->election_date->format('Y-m-d'),
                'total_seats' => $election->total_seats,
            ],
            'predictions' => $predictions,
        ]);
    }

    /**
     * 選挙比較
     */
    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'election1_id' => 'required|exists:elections,id',
            'election2_id' => 'required|exists:elections,id',
        ]);

        $comparison = $this->analysisService->compareElections(
            $request->election1_id,
            $request->election2_id
        );

        return response()->json([
            'status' => 'success',
            'data' => $comparison,
        ]);
    }

    /**
     * 世論調査データ一覧
     */
    public function pollData(Request $request): JsonResponse
    {
        $electionId = $request->input('election_id');
        $source = $request->input('source');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $pollData = $this->dataService->getPollData($electionId, $source, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $pollData,
            'available_sources' => PollData::getAvailableSources(),
        ]);
    }

    /**
     * 世論調査データを登録
     */
    public function storePollData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'party_id' => 'required|exists:political_parties,id',
            'election_id' => 'nullable|exists:elections,id',
            'source' => 'required|string|max:100',
            'poll_type' => 'required|in:phone,online,exit_poll,mixed',
            'survey_start_date' => 'required|date',
            'survey_end_date' => 'required|date|after_or_equal:survey_start_date',
            'support_rate' => 'required|numeric|min:0|max:100',
            'margin_of_error' => 'nullable|numeric|min:0|max:10',
            'sample_size' => 'nullable|integer|min:1',
            'response_rate' => 'nullable|numeric|min:0|max:100',
            'demographic_breakdown' => 'nullable|array',
            'regional_breakdown' => 'nullable|array',
            'notes' => 'nullable|string|max:2000',
        ]);

        $pollData = PollData::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => '世論調査データを登録しました',
            'data' => $pollData->load('party'),
        ], 201);
    }

    /**
     * 政党トレンド分析
     */
    public function partyTrend(Request $request, PoliticalParty $party): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date|after_or_equal:1900-01-01',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date', '2010-01-01'));
        $endDate = Carbon::parse($request->input('end_date', now()));

        $trends = $this->analysisService->analyzePollTrends($party->id, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'party' => [
                'id' => $party->id,
                'name' => $party->name,
                'color' => $party->color,
            ],
            'trends' => $trends,
        ]);
    }

    /**
     * 予測精度検証
     */
    public function validateAccuracy(Election $election): JsonResponse
    {
        $validation = $this->analysisService->validatePredictionAccuracy($election->id);

        return response()->json([
            'status' => 'success',
            'data' => $validation,
        ]);
    }

    /**
     * 政党一覧
     */
    public function parties(Request $request): JsonResponse
    {
        $activeOnly = $request->boolean('active_only', true);
        $parties = $this->dataService->getParties($activeOnly);

        return response()->json([
            'status' => 'success',
            'data' => $parties,
        ]);
    }

    /**
     * 政党登録
     */
    public function storeParty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:political_parties,name',
            'short_name' => 'nullable|string|max:20',
            'english_name' => 'nullable|string|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'founded_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $party = PoliticalParty::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => '政党を登録しました',
            'data' => $party,
        ], 201);
    }

    /**
     * 選挙区一覧
     */
    public function districts(Request $request): JsonResponse
    {
        $houseType = $request->input('house_type');
        $districtType = $request->input('district_type');

        $districts = $this->dataService->getDistricts($houseType, $districtType);

        return response()->json([
            'status' => 'success',
            'data' => $districts,
        ]);
    }

    /**
     * 選挙登録
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|in:house_of_representatives,house_of_councillors',
            'election_date' => 'required|date',
            'announcement_date' => 'nullable|date|before:election_date',
            'total_seats' => 'required|integer|min:1',
            'single_seat_districts' => 'nullable|integer|min:0',
            'proportional_seats' => 'nullable|integer|min:0',
            'voter_turnout' => 'nullable|numeric|min:0|max:100',
            'total_voters' => 'nullable|integer|min:0',
            'total_votes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $election = Election::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => '選挙を登録しました',
            'data' => $election,
        ], 201);
    }

    /**
     * 選挙結果登録
     */
    public function storeResult(Request $request, Election $election): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:election_districts,id',
            'party_id' => 'required|exists:political_parties,id',
            'candidate_name' => 'nullable|string|max:100',
            'votes' => 'required|integer|min:0',
            'vote_share' => 'nullable|numeric|min:0|max:100',
            'seats_won' => 'required|integer|min:0',
            'is_winner' => 'boolean',
            'rank' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:2000',
        ]);

        $result = $election->results()->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => '選挙結果を登録しました',
            'data' => $result->load(['party', 'district']),
        ], 201);
    }

    /**
     * CSVインポート
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'type' => 'required|in:election,poll',
            'election_type' => 'required_if:type,election|in:house_of_representatives,house_of_councillors',
        ]);

        $filePath = $request->file('file')->getRealPath();

        if ($request->type === 'election') {
            $result = $this->dataService->importElectionDataFromCsv($filePath, $request->election_type);
        } else {
            $result = $this->dataService->importPollDataFromCsv($filePath);
        }

        return response()->json($result);
    }

    /**
     * レポートエクスポート
     */
    public function exportReport(Request $request, Election $election)
    {
        $format = $request->input('format', 'json');

        $data = [
            'election' => $election->toArray(),
            'results' => $this->dataService->getElectionResults($election->id),
            'predictions' => SeatPrediction::with('party')
                ->where('election_id', $election->id)
                ->orderBy('predicted_seats', 'desc')
                ->get()
                ->toArray(),
            'generated_at' => now()->toDateTimeString(),
        ];

        if ($format === 'csv') {
            return $this->generateCsvReport($election, $data);
        }

        return response()->json($data);
    }

    /**
     * CSVレポート生成
     */
    private function generateCsvReport(Election $election, array $data)
    {
        $filename = "election_report_{$election->id}_" . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー行
            fputcsv($file, ['政党名', '獲得議席', '得票数', '小選挙区', '比例代表']);

            // データ行
            foreach ($data['results'] as $partyName => $result) {
                fputcsv($file, [
                    $partyName,
                    $result['total_seats'],
                    $result['total_votes'],
                    '-',
                    '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
