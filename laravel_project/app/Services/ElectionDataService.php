<?php

namespace App\Services;

use App\Models\Election;
use App\Models\ElectionDistrict;
use App\Models\ElectionResult;
use App\Models\PoliticalParty;
use App\Models\PollData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ElectionDataService
{
    /**
     * 外部APIから選挙データを取得（シミュレーション）
     * 実際の運用では、総務省や各報道機関のAPIに接続
     */
    public function fetchElectionData(string $electionType, int $year): array
    {
        // 注: 日本には公式の選挙APIがないため、
        // 実装では総務省の選挙結果データやNHK等のAPIを想定
        // ここではシミュレーションデータを返す

        Log::info("選挙データ取得開始: {$electionType}, {$year}年");

        return [
            'status' => 'success',
            'message' => 'データ取得には外部APIとの連携が必要です。',
            'data' => null,
        ];
    }

    /**
     * 世論調査データを取得（複数ソースから）
     */
    public function fetchPollData(string $source, Carbon $startDate, Carbon $endDate): array
    {
        Log::info("世論調査データ取得: {$source}, {$startDate->format('Y-m-d')} - {$endDate->format('Y-m-d')}");

        // 各報道機関のAPIエンドポイント（実際の実装では設定ファイルから取得）
        $endpoints = [
            'NHK' => config('services.election.nhk_api_url', ''),
            '読売新聞' => config('services.election.yomiuri_api_url', ''),
            '朝日新聞' => config('services.election.asahi_api_url', ''),
            '毎日新聞' => config('services.election.mainichi_api_url', ''),
            '日本経済新聞' => config('services.election.nikkei_api_url', ''),
            '産経新聞' => config('services.election.sankei_api_url', ''),
            '共同通信' => config('services.election.kyodo_api_url', ''),
            '時事通信' => config('services.election.jiji_api_url', ''),
        ];

        return [
            'status' => 'info',
            'message' => '世論調査データは各報道機関のAPIまたは手動入力が必要です。',
            'available_sources' => array_keys($endpoints),
        ];
    }

    /**
     * 選挙データをインポート（CSVファイルから）
     */
    public function importElectionDataFromCsv(string $filePath, string $electionType): array
    {
        $imported = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            if (!file_exists($filePath)) {
                throw new \Exception("指定されたファイルが見つかりません");
            }

            $handle = fopen($filePath, 'r');
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new \Exception("CSVファイルのヘッダーが読み取れません");
            }

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) {
                    $errors[] = "行 " . ($imported + 1) . ": 列数が不正です";
                    continue;
                }

                $data = array_combine($header, $row);

                // 選挙結果データをインポート
                $this->importElectionResultRow($data, $electionType);
                $imported++;
            }

            fclose($handle);
            DB::commit();

            return [
                'status' => 'success',
                'imported' => $imported,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("CSVインポートエラー: " . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'CSVインポートに失敗しました',
                'imported' => $imported,
            ];
        }
    }

    /**
     * 世論調査データをインポート
     */
    public function importPollDataFromCsv(string $filePath): array
    {
        $imported = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            if (!file_exists($filePath)) {
                throw new \Exception("指定されたファイルが見つかりません");
            }

            $handle = fopen($filePath, 'r');
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new \Exception("CSVファイルのヘッダーが読み取れません");
            }

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($header)) {
                    $errors[] = "行 " . ($imported + 1) . ": 列数が不正です";
                    continue;
                }

                $data = array_combine($header, $row);

                $party = PoliticalParty::where('name', $data['party_name'])->first();
                if (!$party) {
                    $errors[] = "政党が見つかりません: {$data['party_name']}";
                    continue;
                }

                PollData::create([
                    'party_id' => $party->id,
                    'source' => $data['source'],
                    'poll_type' => $data['poll_type'] ?? 'phone',
                    'survey_start_date' => $data['survey_start_date'],
                    'survey_end_date' => $data['survey_end_date'],
                    'support_rate' => $data['support_rate'],
                    'margin_of_error' => $data['margin_of_error'] ?? null,
                    'sample_size' => $data['sample_size'] ?? null,
                ]);

                $imported++;
            }

            fclose($handle);
            DB::commit();

            return [
                'status' => 'success',
                'imported' => $imported,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("世論調査データインポートエラー: " . $e->getMessage());

            return [
                'status' => 'error',
                'message' => '世論調査データインポートに失敗しました',
                'imported' => $imported,
            ];
        }
    }

    /**
     * 選挙結果行をインポート
     */
    private function importElectionResultRow(array $data, string $electionType): void
    {
        // 選挙を取得または作成
        $election = Election::firstOrCreate(
            [
                'election_date' => $data['election_date'],
                'type' => $electionType,
            ],
            [
                'name' => $data['election_name'],
                'total_seats' => $data['total_seats'] ?? 0,
            ]
        );

        // 選挙区を取得または作成
        $district = ElectionDistrict::firstOrCreate(
            [
                'name' => $data['district_name'],
                'house_type' => $electionType,
            ],
            [
                'type' => $data['district_type'] ?? 'single_seat',
                'prefecture' => $data['prefecture'] ?? null,
                'seats' => $data['district_seats'] ?? 1,
            ]
        );

        // 政党を取得または作成
        $party = PoliticalParty::firstOrCreate(
            ['name' => $data['party_name']],
            [
                'short_name' => $data['party_short_name'] ?? null,
                'is_active' => true,
            ]
        );

        // 選挙結果を作成
        ElectionResult::create([
            'election_id' => $election->id,
            'district_id' => $district->id,
            'party_id' => $party->id,
            'candidate_name' => $data['candidate_name'] ?? null,
            'votes' => $data['votes'] ?? 0,
            'vote_share' => $data['vote_share'] ?? null,
            'seats_won' => $data['seats_won'] ?? 0,
            'is_winner' => ($data['is_winner'] ?? 0) == 1,
            'rank' => $data['rank'] ?? null,
        ]);
    }

    /**
     * 特定期間の選挙一覧を取得
     */
    public function getElections(string $type = null, int $fromYear = 2010, int $toYear = 2026): array
    {
        $query = Election::query()
            ->whereBetween('election_date', [
                Carbon::create($fromYear, 1, 1),
                Carbon::create($toYear, 12, 31),
            ])
            ->orderBy('election_date', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get()->toArray();
    }

    /**
     * 選挙区一覧を取得
     */
    public function getDistricts(string $houseType = null, string $districtType = null): array
    {
        $query = ElectionDistrict::query()->active();

        if ($houseType) {
            $query->where('house_type', $houseType);
        }

        if ($districtType) {
            $query->where('type', $districtType);
        }

        return $query->orderBy('prefecture')->orderBy('name')->get()->toArray();
    }

    /**
     * 政党一覧を取得
     */
    public function getParties(bool $activeOnly = true): array
    {
        $query = PoliticalParty::query();

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('name')->get()->toArray();
    }

    /**
     * 特定選挙の結果を取得
     */
    public function getElectionResults(int $electionId): array
    {
        return ElectionResult::with(['party', 'district'])
            ->where('election_id', $electionId)
            ->get()
            ->groupBy('party.name')
            ->map(function ($results) {
                return [
                    'total_votes' => $results->sum('votes'),
                    'total_seats' => $results->sum('seats_won'),
                    'districts' => $results->count(),
                    'wins' => $results->where('is_winner', true)->count(),
                ];
            })
            ->toArray();
    }

    /**
     * 世論調査データを取得
     */
    public function getPollData(
        int $electionId = null,
        string $source = null,
        Carbon $startDate = null,
        Carbon $endDate = null
    ): array {
        $query = PollData::with('party');

        if ($electionId) {
            $query->where('election_id', $electionId);
        }

        if ($source) {
            $query->where('source', $source);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('survey_end_date', [$startDate, $endDate]);
        }

        return $query->orderBy('survey_end_date', 'desc')->get()->toArray();
    }
}
