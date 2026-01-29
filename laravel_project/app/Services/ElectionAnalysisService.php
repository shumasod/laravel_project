<?php

namespace App\Services;

use App\Models\Election;
use App\Models\ElectionDistrict;
use App\Models\ElectionResult;
use App\Models\PoliticalParty;
use App\Models\PollData;
use App\Models\SeatPrediction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ElectionAnalysisService
{
    /**
     * 分析パラメータ
     */
    private array $analysisParams = [
        // 世論調査の重み付け（ソース別信頼度）
        'source_weights' => [
            'NHK' => 1.0,
            '読売新聞' => 0.95,
            '朝日新聞' => 0.95,
            '毎日新聞' => 0.90,
            '日本経済新聞' => 0.92,
            '産経新聞' => 0.88,
            '共同通信' => 0.93,
            '時事通信' => 0.91,
        ],
        // 調査方法の重み付け
        'poll_type_weights' => [
            'phone' => 0.85,
            'online' => 0.75,
            'exit_poll' => 1.0,
            'mixed' => 0.80,
        ],
        // 時間減衰係数（古いデータほど信頼度が下がる）
        'time_decay_days' => 30, // 30日で信頼度半減
        // 小選挙区の現職効果
        'incumbent_bonus' => 0.03, // 3%ポイント
        // 比例代表の閾値
        'proportional_threshold' => 0.02, // 2%未満は議席なし
    ];

    /**
     * 衆議院の議席配分
     */
    private array $hrSeats = [
        'single' => 289, // 小選挙区
        'proportional' => 176, // 比例代表
        'total' => 465,
    ];

    /**
     * 参議院の議席配分（改選議席）
     */
    private array $hcSeats = [
        'single' => 74, // 選挙区（改選）
        'proportional' => 50, // 比例代表（改選）
        'total' => 124,
    ];

    /**
     * 比例ブロック別議席数（衆議院）
     */
    private array $proportionalBlocks = [
        '北海道' => 8,
        '東北' => 13,
        '北関東' => 19,
        '南関東' => 22,
        '東京' => 17,
        '北陸信越' => 11,
        '東海' => 21,
        '近畿' => 28,
        '中国' => 11,
        '四国' => 6,
        '九州' => 20,
    ];

    /**
     * 議席予測を実行
     */
    public function predictSeats(int $electionId): array
    {
        $election = Election::findOrFail($electionId);
        $isHouseOfReps = $election->type === Election::TYPE_HOUSE_OF_REPRESENTATIVES;
        $seats = $isHouseOfReps ? $this->hrSeats : $this->hcSeats;

        // 世論調査データを取得
        $pollData = $this->getWeightedPollData($electionId, $election->election_date);

        if (empty($pollData)) {
            // 世論調査データがない場合は過去の選挙結果から予測
            $pollData = $this->estimateFromHistoricalData($election);
        }

        // 小選挙区の予測
        $singleSeatPrediction = $this->predictSingleSeatDistricts(
            $election,
            $pollData,
            $seats['single']
        );

        // 比例代表の予測
        $proportionalPrediction = $this->predictProportionalSeats(
            $election,
            $pollData,
            $seats['proportional'],
            $isHouseOfReps
        );

        // 予測結果を統合
        $predictions = $this->combinePredictions(
            $singleSeatPrediction,
            $proportionalPrediction,
            $pollData
        );

        // 予測結果を保存
        $this->savePredictions($electionId, $predictions);

        return [
            'election' => $election->toArray(),
            'predictions' => $predictions,
            'total_seats' => $seats['total'],
            'analysis_date' => now()->toDateTimeString(),
            'methodology' => $this->getMethodologyDescription(),
        ];
    }

    /**
     * 重み付けされた世論調査データを取得
     */
    private function getWeightedPollData(int $electionId, $electionDate): array
    {
        $cutoffDate = Carbon::parse($electionDate)->subDays(90);

        $polls = PollData::with('party')
            ->where(function ($query) use ($electionId) {
                $query->where('election_id', $electionId)
                    ->orWhereNull('election_id');
            })
            ->where('survey_end_date', '>=', $cutoffDate)
            ->where('survey_end_date', '<=', $electionDate)
            ->get();

        $weightedData = [];

        foreach ($polls as $poll) {
            $partyName = $poll->party->name;

            // 各種重み付けを適用
            $sourceWeight = $this->analysisParams['source_weights'][$poll->source] ?? 0.80;
            $typeWeight = $this->analysisParams['poll_type_weights'][$poll->poll_type] ?? 0.80;

            // 時間減衰を計算
            $daysDiff = Carbon::parse($electionDate)->diffInDays($poll->survey_end_date);
            $timeDecay = pow(0.5, $daysDiff / $this->analysisParams['time_decay_days']);

            // サンプルサイズによる重み
            $sampleWeight = $poll->sample_size ? min(1.0, $poll->sample_size / 2000) : 0.7;

            $totalWeight = $sourceWeight * $typeWeight * $timeDecay * $sampleWeight;

            if (!isset($weightedData[$partyName])) {
                $weightedData[$partyName] = [
                    'party_id' => $poll->party_id,
                    'weighted_sum' => 0,
                    'weight_total' => 0,
                    'poll_count' => 0,
                    'sources' => [],
                ];
            }

            $weightedData[$partyName]['weighted_sum'] += $poll->support_rate * $totalWeight;
            $weightedData[$partyName]['weight_total'] += $totalWeight;
            $weightedData[$partyName]['poll_count']++;
            $weightedData[$partyName]['sources'][] = $poll->source;
        }

        // 加重平均を計算
        foreach ($weightedData as $partyName => &$data) {
            if ($data['weight_total'] > 0) {
                $data['support_rate'] = $data['weighted_sum'] / $data['weight_total'];
            } else {
                $data['support_rate'] = 0;
            }
            $data['sources'] = array_unique($data['sources']);
        }

        return $weightedData;
    }

    /**
     * 過去のデータから推定
     */
    private function estimateFromHistoricalData(Election $election): array
    {
        // 同タイプの直近の選挙結果を取得
        $previousElection = Election::where('type', $election->type)
            ->where('election_date', '<', $election->election_date)
            ->orderBy('election_date', 'desc')
            ->first();

        if (!$previousElection) {
            return [];
        }

        $results = ElectionResult::with('party')
            ->where('election_id', $previousElection->id)
            ->get()
            ->groupBy('party.name');

        $estimated = [];
        $totalSeats = $previousElection->total_seats ?: 1;

        foreach ($results as $partyName => $partyResults) {
            $seatsWon = $partyResults->sum('seats_won');
            $estimated[$partyName] = [
                'party_id' => $partyResults->first()->party_id,
                'support_rate' => ($seatsWon / $totalSeats) * 100,
                'from_historical' => true,
                'source_election' => $previousElection->name,
            ];
        }

        return $estimated;
    }

    /**
     * 小選挙区の議席予測
     */
    private function predictSingleSeatDistricts(
        Election $election,
        array $pollData,
        int $totalSeats
    ): array {
        $predictions = [];

        // 各政党の小選挙区予測
        // 支持率と過去の当選実績から予測
        $totalSupport = array_sum(array_column($pollData, 'support_rate'));

        if ($totalSupport <= 0) {
            return $predictions;
        }

        // 過去の小選挙区勝率を取得
        $historicalWinRates = $this->getHistoricalWinRates($election->type);

        foreach ($pollData as $partyName => $data) {
            $supportShare = $data['support_rate'] / $totalSupport;

            // 小選挙区では支持率に対して非線形に議席が決まる傾向
            // （第一党が過大代表される）
            $adjustedShare = $this->applyWinnerBonus($supportShare, $pollData);

            // 過去の勝率も考慮
            $historicalFactor = $historicalWinRates[$partyName] ?? 0.5;
            $finalShare = ($adjustedShare * 0.7) + ($historicalFactor * 0.3);

            $predictedSeats = (int) round($totalSeats * $finalShare);

            $predictions[$partyName] = [
                'party_id' => $data['party_id'],
                'seats' => $predictedSeats,
                'support_share' => round($supportShare * 100, 2),
                'adjusted_share' => round($adjustedShare * 100, 2),
            ];
        }

        // 合計が総議席数と一致するよう調整
        $predictions = $this->adjustToTotalSeats($predictions, $totalSeats);

        return $predictions;
    }

    /**
     * 第一党ボーナス（小選挙区制の特性）
     */
    private function applyWinnerBonus(float $supportShare, array $pollData): float
    {
        // 支持率トップの政党を特定
        $maxSupport = max(array_column($pollData, 'support_rate'));
        $isTopParty = false;

        foreach ($pollData as $data) {
            if ($data['support_rate'] == $maxSupport) {
                $isTopParty = true;
                break;
            }
        }

        if ($supportShare > 0.3) {
            // 30%以上の支持率なら議席獲得率が上昇
            return min(0.7, $supportShare * 1.5);
        } elseif ($supportShare > 0.2) {
            return $supportShare * 1.2;
        } elseif ($supportShare < 0.1) {
            // 10%未満は大幅に減少
            return $supportShare * 0.5;
        }

        return $supportShare;
    }

    /**
     * 過去の小選挙区勝率を取得
     */
    private function getHistoricalWinRates(string $electionType): array
    {
        $results = ElectionResult::join('elections', 'election_results.election_id', '=', 'elections.id')
            ->join('election_districts', 'election_results.district_id', '=', 'election_districts.id')
            ->join('political_parties', 'election_results.party_id', '=', 'political_parties.id')
            ->where('elections.type', $electionType)
            ->where('election_districts.type', 'single_seat')
            ->where('elections.election_date', '>=', Carbon::now()->subYears(16))
            ->selectRaw('political_parties.name,
                         COUNT(*) as total_contests,
                         SUM(CASE WHEN election_results.is_winner = 1 THEN 1 ELSE 0 END) as wins')
            ->groupBy('political_parties.name')
            ->get();

        $winRates = [];
        foreach ($results as $result) {
            if ($result->total_contests > 0) {
                $winRates[$result->name] = $result->wins / $result->total_contests;
            }
        }

        return $winRates;
    }

    /**
     * 比例代表の議席予測
     */
    private function predictProportionalSeats(
        Election $election,
        array $pollData,
        int $totalSeats,
        bool $isHouseOfReps
    ): array {
        $predictions = [];
        $threshold = $this->analysisParams['proportional_threshold'];

        // 有効支持率（閾値以上の政党のみ）
        $validParties = array_filter($pollData, function ($data) use ($threshold) {
            return ($data['support_rate'] / 100) >= $threshold;
        });

        $totalValidSupport = array_sum(array_column($validParties, 'support_rate'));

        if ($totalValidSupport <= 0) {
            return $predictions;
        }

        foreach ($validParties as $partyName => $data) {
            $supportShare = $data['support_rate'] / $totalValidSupport;

            // ドント方式を簡略化して計算
            $predictedSeats = (int) round($totalSeats * $supportShare);

            $predictions[$partyName] = [
                'party_id' => $data['party_id'],
                'seats' => $predictedSeats,
                'support_share' => round($supportShare * 100, 2),
            ];
        }

        // 合計調整
        $predictions = $this->adjustToTotalSeats($predictions, $totalSeats);

        return $predictions;
    }

    /**
     * 議席数の合計を調整
     */
    private function adjustToTotalSeats(array $predictions, int $totalSeats): array
    {
        $currentTotal = array_sum(array_column($predictions, 'seats'));
        $diff = $totalSeats - $currentTotal;

        if ($diff == 0) {
            return $predictions;
        }

        // 支持率順にソート
        uasort($predictions, function ($a, $b) {
            return ($b['support_share'] ?? 0) <=> ($a['support_share'] ?? 0);
        });

        // 差分を上位政党から調整
        $keys = array_keys($predictions);
        $i = 0;
        while ($diff != 0 && $i < count($keys)) {
            $key = $keys[$i % count($keys)];
            if ($diff > 0) {
                $predictions[$key]['seats']++;
                $diff--;
            } elseif ($diff < 0 && $predictions[$key]['seats'] > 0) {
                $predictions[$key]['seats']--;
                $diff++;
            }
            $i++;
        }

        return $predictions;
    }

    /**
     * 予測を統合
     */
    private function combinePredictions(
        array $singleSeatPrediction,
        array $proportionalPrediction,
        array $pollData
    ): array {
        $combined = [];

        $allParties = array_unique(array_merge(
            array_keys($singleSeatPrediction),
            array_keys($proportionalPrediction)
        ));

        foreach ($allParties as $partyName) {
            $singleSeats = $singleSeatPrediction[$partyName]['seats'] ?? 0;
            $proportionalSeats = $proportionalPrediction[$partyName]['seats'] ?? 0;
            $totalSeats = $singleSeats + $proportionalSeats;

            // 予測の誤差範囲を計算（支持率の変動を考慮）
            $marginOfError = $this->calculateMarginOfError($totalSeats, $pollData[$partyName] ?? []);

            $combined[$partyName] = [
                'party_id' => $pollData[$partyName]['party_id']
                    ?? $singleSeatPrediction[$partyName]['party_id']
                    ?? $proportionalPrediction[$partyName]['party_id'],
                'predicted_seats' => $totalSeats,
                'single_seat_prediction' => $singleSeats,
                'proportional_prediction' => $proportionalSeats,
                'min_seats' => max(0, $totalSeats - $marginOfError),
                'max_seats' => $totalSeats + $marginOfError,
                'support_rate' => $pollData[$partyName]['support_rate'] ?? null,
                'poll_count' => $pollData[$partyName]['poll_count'] ?? 0,
                'sources' => $pollData[$partyName]['sources'] ?? [],
            ];
        }

        // 議席数順にソート
        uasort($combined, function ($a, $b) {
            return $b['predicted_seats'] <=> $a['predicted_seats'];
        });

        return $combined;
    }

    /**
     * 誤差範囲を計算
     */
    private function calculateMarginOfError(int $predictedSeats, array $pollData): int
    {
        $pollCount = $pollData['poll_count'] ?? 0;

        // 世論調査が多いほど誤差は小さくなる
        $baseFactor = $pollCount > 5 ? 0.10 : ($pollCount > 2 ? 0.15 : 0.20);

        return (int) ceil($predictedSeats * $baseFactor);
    }

    /**
     * 予測結果を保存
     */
    private function savePredictions(int $electionId, array $predictions): void
    {
        DB::transaction(function () use ($electionId, $predictions) {
            foreach ($predictions as $partyName => $prediction) {
                SeatPrediction::create([
                    'election_id' => $electionId,
                    'party_id' => $prediction['party_id'],
                    'predicted_seats' => $prediction['predicted_seats'],
                    'min_seats' => $prediction['min_seats'],
                    'max_seats' => $prediction['max_seats'],
                    'single_seat_prediction' => $prediction['single_seat_prediction'],
                    'proportional_prediction' => $prediction['proportional_prediction'],
                    'confidence_level' => $this->calculateConfidenceLevel($prediction),
                    'analysis_factors' => [
                        'support_rate' => $prediction['support_rate'],
                        'poll_count' => $prediction['poll_count'],
                        'sources' => $prediction['sources'],
                    ],
                    'methodology' => $this->getMethodologyDescription(),
                    'predicted_at' => now(),
                ]);
            }
        });
    }

    /**
     * 信頼度を計算
     */
    private function calculateConfidenceLevel(array $prediction): float
    {
        $pollCount = $prediction['poll_count'] ?? 0;
        $sourceCount = count($prediction['sources'] ?? []);

        // 基本信頼度
        $confidence = 50.0;

        // 世論調査の数に応じて増加
        $confidence += min(30, $pollCount * 5);

        // ソースの多様性に応じて増加
        $confidence += min(20, $sourceCount * 4);

        return min(95.0, $confidence);
    }

    /**
     * 分析手法の説明
     */
    private function getMethodologyDescription(): string
    {
        return '世論調査加重平均法: ' .
            '(1) 各報道機関の世論調査を信頼度・調査方法・時間経過で重み付け、' .
            '(2) 小選挙区は第一党優位補正と過去勝率を適用、' .
            '(3) 比例代表は閾値(2%)以上の政党で按分、' .
            '(4) 誤差範囲は調査数と多様性から計算。';
    }

    /**
     * 選挙間の比較分析
     */
    public function compareElections(int $election1Id, int $election2Id): array
    {
        $election1 = Election::with('results.party')->findOrFail($election1Id);
        $election2 = Election::with('results.party')->findOrFail($election2Id);

        $results1 = $this->aggregateResultsByParty($election1);
        $results2 = $this->aggregateResultsByParty($election2);

        $comparison = [];
        $allParties = array_unique(array_merge(array_keys($results1), array_keys($results2)));

        foreach ($allParties as $partyName) {
            $seats1 = $results1[$partyName] ?? 0;
            $seats2 = $results2[$partyName] ?? 0;
            $change = $seats2 - $seats1;

            $comparison[$partyName] = [
                'election1_seats' => $seats1,
                'election2_seats' => $seats2,
                'change' => $change,
                'change_percent' => $seats1 > 0 ? round(($change / $seats1) * 100, 2) : null,
            ];
        }

        return [
            'election1' => [
                'name' => $election1->name,
                'date' => $election1->election_date->format('Y-m-d'),
                'turnout' => $election1->voter_turnout,
            ],
            'election2' => [
                'name' => $election2->name,
                'date' => $election2->election_date->format('Y-m-d'),
                'turnout' => $election2->voter_turnout,
            ],
            'comparison' => $comparison,
        ];
    }

    /**
     * 政党別に結果を集計
     */
    private function aggregateResultsByParty(Election $election): array
    {
        return $election->results
            ->groupBy(function ($result) {
                return $result->party->name;
            })
            ->map(function ($results) {
                return $results->sum('seats_won');
            })
            ->toArray();
    }

    /**
     * トレンド分析（世論調査の推移）
     */
    public function analyzePollTrends(int $partyId, Carbon $startDate, Carbon $endDate): array
    {
        $polls = PollData::where('party_id', $partyId)
            ->whereBetween('survey_end_date', [$startDate, $endDate])
            ->orderBy('survey_end_date')
            ->get();

        $trends = [];
        $sources = [];

        foreach ($polls as $poll) {
            $dateKey = $poll->survey_end_date->format('Y-m');

            if (!isset($trends[$dateKey])) {
                $trends[$dateKey] = [
                    'polls' => [],
                    'average' => 0,
                ];
            }

            $trends[$dateKey]['polls'][] = [
                'source' => $poll->source,
                'support_rate' => $poll->support_rate,
                'date' => $poll->survey_end_date->format('Y-m-d'),
            ];

            $sources[$poll->source] = true;
        }

        // 月別平均を計算
        foreach ($trends as $dateKey => &$data) {
            $data['average'] = round(
                array_sum(array_column($data['polls'], 'support_rate')) / count($data['polls']),
                2
            );
        }

        return [
            'party_id' => $partyId,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'trends' => $trends,
            'sources' => array_keys($sources),
            'total_polls' => $polls->count(),
        ];
    }

    /**
     * 過去の予測精度を検証
     */
    public function validatePredictionAccuracy(int $electionId): array
    {
        $predictions = SeatPrediction::with('party')
            ->where('election_id', $electionId)
            ->get();

        $actualResults = ElectionResult::with('party')
            ->where('election_id', $electionId)
            ->get()
            ->groupBy('party_id')
            ->map(function ($results) {
                return $results->sum('seats_won');
            });

        $validation = [];
        $totalError = 0;
        $count = 0;

        foreach ($predictions as $prediction) {
            $actual = $actualResults[$prediction->party_id] ?? 0;
            $predicted = $prediction->predicted_seats;
            $error = abs($actual - $predicted);

            $validation[$prediction->party->name] = [
                'predicted' => $predicted,
                'actual' => $actual,
                'error' => $error,
                'within_range' => $actual >= $prediction->min_seats && $actual <= $prediction->max_seats,
                'accuracy' => $prediction->calculateAccuracy(),
            ];

            $totalError += $error;
            $count++;
        }

        return [
            'election_id' => $electionId,
            'validation' => $validation,
            'average_error' => $count > 0 ? round($totalError / $count, 2) : 0,
            'predictions_within_range' => count(array_filter($validation, fn($v) => $v['within_range'])),
            'total_predictions' => $count,
        ];
    }
}
