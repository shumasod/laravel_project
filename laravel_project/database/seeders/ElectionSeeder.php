<?php

namespace Database\Seeders;

use App\Models\Election;
use App\Models\ElectionDistrict;
use App\Models\ElectionResult;
use App\Models\PoliticalParty;
use App\Models\PollData;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 政党データ
        $this->seedParties();

        // 選挙区データ
        $this->seedDistricts();

        // 選挙データ
        $this->seedElections();

        // 選挙結果データ
        $this->seedElectionResults();

        // 世論調査データ
        $this->seedPollData();
    }

    /**
     * 政党データの投入
     */
    private function seedParties(): void
    {
        $parties = [
            // 主要政党
            ['name' => '自由民主党', 'short_name' => '自民', 'color' => '#E53935', 'founded_date' => '1955-11-15'],
            ['name' => '立憲民主党', 'short_name' => '立民', 'color' => '#1565C0', 'founded_date' => '2020-09-15'],
            ['name' => '公明党', 'short_name' => '公明', 'color' => '#FF9800', 'founded_date' => '1964-11-17'],
            ['name' => '日本維新の会', 'short_name' => '維新', 'color' => '#4CAF50', 'founded_date' => '2015-11-02'],
            ['name' => '日本共産党', 'short_name' => '共産', 'color' => '#D32F2F', 'founded_date' => '1922-07-15'],
            ['name' => '国民民主党', 'short_name' => '国民', 'color' => '#FFC107', 'founded_date' => '2020-09-11'],
            ['name' => 'れいわ新選組', 'short_name' => 'れいわ', 'color' => '#E91E63', 'founded_date' => '2019-04-01'],
            ['name' => '社会民主党', 'short_name' => '社民', 'color' => '#2196F3', 'founded_date' => '1996-01-19'],
            ['name' => 'NHK党', 'short_name' => 'N党', 'color' => '#795548', 'founded_date' => '2013-06-17'],
            ['name' => '参政党', 'short_name' => '参政', 'color' => '#FF5722', 'founded_date' => '2020-04-11'],

            // 過去に存在した政党
            ['name' => '民主党', 'short_name' => '民主', 'color' => '#00BCD4', 'founded_date' => '1998-04-27', 'dissolved_date' => '2016-03-27', 'is_active' => false],
            ['name' => '民進党', 'short_name' => '民進', 'color' => '#00BCD4', 'founded_date' => '2016-03-27', 'dissolved_date' => '2018-05-07', 'is_active' => false],
            ['name' => '日本未来の党', 'short_name' => '未来', 'color' => '#8BC34A', 'founded_date' => '2012-11-27', 'dissolved_date' => '2012-12-28', 'is_active' => false],
            ['name' => 'みんなの党', 'short_name' => 'みんな', 'color' => '#9C27B0', 'founded_date' => '2009-08-08', 'dissolved_date' => '2014-11-28', 'is_active' => false],
            ['name' => '希望の党', 'short_name' => '希望', 'color' => '#009688', 'founded_date' => '2017-09-25', 'dissolved_date' => '2018-05-07', 'is_active' => false],

            // 無所属・その他
            ['name' => '無所属', 'short_name' => '無', 'color' => '#9E9E9E', 'founded_date' => null],
            ['name' => 'その他', 'short_name' => '他', 'color' => '#607D8B', 'founded_date' => null],
        ];

        foreach ($parties as $party) {
            PoliticalParty::firstOrCreate(
                ['name' => $party['name']],
                [
                    'short_name' => $party['short_name'],
                    'color' => $party['color'],
                    'founded_date' => $party['founded_date'] ?? null,
                    'dissolved_date' => $party['dissolved_date'] ?? null,
                    'is_active' => $party['is_active'] ?? true,
                ]
            );
        }
    }

    /**
     * 選挙区データの投入
     */
    private function seedDistricts(): void
    {
        $prefectures = [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
            '岐阜県', '静岡県', '愛知県', '三重県',
            '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
            '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県',
            '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県',
        ];

        // 衆議院小選挙区（各都道府県に1〜複数）
        $hrSingleSeats = [
            '北海道' => 12, '青森県' => 3, '岩手県' => 3, '宮城県' => 6, '秋田県' => 3,
            '山形県' => 3, '福島県' => 5, '茨城県' => 7, '栃木県' => 5, '群馬県' => 5,
            '埼玉県' => 15, '千葉県' => 13, '東京都' => 25, '神奈川県' => 18,
            '新潟県' => 6, '富山県' => 3, '石川県' => 3, '福井県' => 2, '山梨県' => 2, '長野県' => 5,
            '岐阜県' => 5, '静岡県' => 8, '愛知県' => 15, '三重県' => 5,
            '滋賀県' => 4, '京都府' => 6, '大阪府' => 19, '兵庫県' => 12, '奈良県' => 3, '和歌山県' => 3,
            '鳥取県' => 2, '島根県' => 2, '岡山県' => 5, '広島県' => 7, '山口県' => 4,
            '徳島県' => 2, '香川県' => 3, '愛媛県' => 4, '高知県' => 2,
            '福岡県' => 11, '佐賀県' => 2, '長崎県' => 4, '熊本県' => 5, '大分県' => 3, '宮崎県' => 3, '鹿児島県' => 5, '沖縄県' => 4,
        ];

        foreach ($hrSingleSeats as $prefecture => $districts) {
            for ($i = 1; $i <= $districts; $i++) {
                ElectionDistrict::firstOrCreate([
                    'name' => "{$prefecture}{$i}区",
                    'house_type' => 'house_of_representatives',
                    'type' => 'single_seat',
                ], [
                    'prefecture' => $prefecture,
                    'seats' => 1,
                    'is_active' => true,
                ]);
            }
        }

        // 衆議院比例代表ブロック
        $hrProportionalBlocks = [
            '北海道ブロック' => 8,
            '東北ブロック' => 13,
            '北関東ブロック' => 19,
            '南関東ブロック' => 22,
            '東京ブロック' => 17,
            '北陸信越ブロック' => 11,
            '東海ブロック' => 21,
            '近畿ブロック' => 28,
            '中国ブロック' => 11,
            '四国ブロック' => 6,
            '九州ブロック' => 20,
        ];

        foreach ($hrProportionalBlocks as $name => $seats) {
            ElectionDistrict::firstOrCreate([
                'name' => $name,
                'house_type' => 'house_of_representatives',
                'type' => 'proportional',
            ], [
                'seats' => $seats,
                'is_active' => true,
            ]);
        }

        // 参議院選挙区（都道府県単位）
        $hcSeats = [
            '北海道' => 3, '青森県' => 1, '岩手県' => 1, '宮城県' => 2, '秋田県' => 1,
            '山形県' => 1, '福島県' => 2, '茨城県' => 2, '栃木県' => 1, '群馬県' => 1,
            '埼玉県' => 4, '千葉県' => 3, '東京都' => 6, '神奈川県' => 4,
            '新潟県' => 2, '富山県' => 1, '石川県' => 1, '福井県' => 1, '山梨県' => 1, '長野県' => 2,
            '岐阜県' => 1, '静岡県' => 2, '愛知県' => 4, '三重県' => 1,
            '滋賀県' => 1, '京都府' => 2, '大阪府' => 4, '兵庫県' => 3, '奈良県' => 1, '和歌山県' => 1,
            '鳥取・島根' => 1, '岡山県' => 1, '広島県' => 2, '山口県' => 1,
            '徳島・高知' => 1, '香川県' => 1, '愛媛県' => 1,
            '福岡県' => 3, '佐賀県' => 1, '長崎県' => 1, '熊本県' => 1, '大分県' => 1, '宮崎県' => 1, '鹿児島県' => 1, '沖縄県' => 1,
        ];

        foreach ($hcSeats as $name => $seats) {
            ElectionDistrict::firstOrCreate([
                'name' => "{$name}選挙区",
                'house_type' => 'house_of_councillors',
                'type' => 'single_seat',
            ], [
                'prefecture' => str_contains($name, '・') ? null : $name,
                'seats' => $seats,
                'is_active' => true,
            ]);
        }

        // 参議院比例代表（全国区）
        ElectionDistrict::firstOrCreate([
            'name' => '比例代表',
            'house_type' => 'house_of_councillors',
            'type' => 'proportional',
        ], [
            'seats' => 50,
            'is_active' => true,
        ]);
    }

    /**
     * 選挙データの投入
     */
    private function seedElections(): void
    {
        // 衆議院選挙 (2010-2026)
        $hrElections = [
            [
                'name' => '第45回衆議院議員総選挙',
                'election_date' => '2009-08-30',
                'total_seats' => 480,
                'single_seat_districts' => 300,
                'proportional_seats' => 180,
                'voter_turnout' => 69.28,
            ],
            [
                'name' => '第46回衆議院議員総選挙',
                'election_date' => '2012-12-16',
                'total_seats' => 480,
                'single_seat_districts' => 300,
                'proportional_seats' => 180,
                'voter_turnout' => 59.32,
            ],
            [
                'name' => '第47回衆議院議員総選挙',
                'election_date' => '2014-12-14',
                'total_seats' => 475,
                'single_seat_districts' => 295,
                'proportional_seats' => 180,
                'voter_turnout' => 52.66,
            ],
            [
                'name' => '第48回衆議院議員総選挙',
                'election_date' => '2017-10-22',
                'total_seats' => 465,
                'single_seat_districts' => 289,
                'proportional_seats' => 176,
                'voter_turnout' => 53.68,
            ],
            [
                'name' => '第49回衆議院議員総選挙',
                'election_date' => '2021-10-31',
                'total_seats' => 465,
                'single_seat_districts' => 289,
                'proportional_seats' => 176,
                'voter_turnout' => 55.93,
            ],
            [
                'name' => '第50回衆議院議員総選挙',
                'election_date' => '2024-10-27',
                'total_seats' => 465,
                'single_seat_districts' => 289,
                'proportional_seats' => 176,
                'voter_turnout' => 53.85,
            ],
        ];

        foreach ($hrElections as $election) {
            Election::firstOrCreate(
                ['name' => $election['name']],
                array_merge($election, ['type' => 'house_of_representatives'])
            );
        }

        // 参議院選挙 (2010-2026)
        $hcElections = [
            [
                'name' => '第22回参議院議員通常選挙',
                'election_date' => '2010-07-11',
                'total_seats' => 121,
                'single_seat_districts' => 73,
                'proportional_seats' => 48,
                'voter_turnout' => 57.92,
            ],
            [
                'name' => '第23回参議院議員通常選挙',
                'election_date' => '2013-07-21',
                'total_seats' => 121,
                'single_seat_districts' => 73,
                'proportional_seats' => 48,
                'voter_turnout' => 52.61,
            ],
            [
                'name' => '第24回参議院議員通常選挙',
                'election_date' => '2016-07-10',
                'total_seats' => 121,
                'single_seat_districts' => 73,
                'proportional_seats' => 48,
                'voter_turnout' => 54.70,
            ],
            [
                'name' => '第25回参議院議員通常選挙',
                'election_date' => '2019-07-21',
                'total_seats' => 124,
                'single_seat_districts' => 74,
                'proportional_seats' => 50,
                'voter_turnout' => 48.80,
            ],
            [
                'name' => '第26回参議院議員通常選挙',
                'election_date' => '2022-07-10',
                'total_seats' => 125,
                'single_seat_districts' => 75,
                'proportional_seats' => 50,
                'voter_turnout' => 52.05,
            ],
            [
                'name' => '第27回参議院議員通常選挙',
                'election_date' => '2025-07-27',
                'total_seats' => 125,
                'single_seat_districts' => 75,
                'proportional_seats' => 50,
                'voter_turnout' => null, // 未実施
            ],
        ];

        foreach ($hcElections as $election) {
            Election::firstOrCreate(
                ['name' => $election['name']],
                array_merge($election, ['type' => 'house_of_councillors'])
            );
        }
    }

    /**
     * 選挙結果データの投入（サマリー）
     */
    private function seedElectionResults(): void
    {
        // 第49回衆議院選挙（2021年）の結果例
        $election2021 = Election::where('name', '第49回衆議院議員総選挙')->first();
        if ($election2021) {
            $results2021 = [
                ['party' => '自由民主党', 'single' => 189, 'proportional' => 72, 'votes' => 19914883],
                ['party' => '立憲民主党', 'single' => 57, 'proportional' => 39, 'votes' => 11492095],
                ['party' => '公明党', 'single' => 9, 'proportional' => 23, 'votes' => 7114282],
                ['party' => '日本維新の会', 'single' => 16, 'proportional' => 25, 'votes' => 8050830],
                ['party' => '日本共産党', 'single' => 1, 'proportional' => 9, 'votes' => 4166076],
                ['party' => '国民民主党', 'single' => 6, 'proportional' => 5, 'votes' => 2593396],
                ['party' => 'れいわ新選組', 'single' => 0, 'proportional' => 3, 'votes' => 2215648],
                ['party' => '社会民主党', 'single' => 1, 'proportional' => 0, 'votes' => 1018588],
            ];

            $this->createElectionResults($election2021, $results2021);
        }

        // 第50回衆議院選挙（2024年）の結果例
        $election2024 = Election::where('name', '第50回衆議院議員総選挙')->first();
        if ($election2024) {
            $results2024 = [
                ['party' => '自由民主党', 'single' => 132, 'proportional' => 59, 'votes' => 14586000],
                ['party' => '立憲民主党', 'single' => 104, 'proportional' => 44, 'votes' => 11300000],
                ['party' => '日本維新の会', 'single' => 23, 'proportional' => 15, 'votes' => 5100000],
                ['party' => '公明党', 'single' => 4, 'proportional' => 20, 'votes' => 5960000],
                ['party' => '国民民主党', 'single' => 17, 'proportional' => 11, 'votes' => 3170000],
                ['party' => '日本共産党', 'single' => 1, 'proportional' => 7, 'votes' => 3360000],
                ['party' => 'れいわ新選組', 'single' => 0, 'proportional' => 9, 'votes' => 3800000],
                ['party' => '社会民主党', 'single' => 1, 'proportional' => 0, 'votes' => 940000],
                ['party' => '参政党', 'single' => 0, 'proportional' => 3, 'votes' => 1870000],
            ];

            $this->createElectionResults($election2024, $results2024);
        }

        // 第26回参議院選挙（2022年）の結果例
        $election2022 = Election::where('name', '第26回参議院議員通常選挙')->first();
        if ($election2022) {
            $results2022 = [
                ['party' => '自由民主党', 'single' => 45, 'proportional' => 18, 'votes' => 18256245],
                ['party' => '立憲民主党', 'single' => 10, 'proportional' => 7, 'votes' => 6771945],
                ['party' => '公明党', 'single' => 7, 'proportional' => 6, 'votes' => 6181431],
                ['party' => '日本維新の会', 'single' => 4, 'proportional' => 8, 'votes' => 7845995],
                ['party' => '日本共産党', 'single' => 0, 'proportional' => 4, 'votes' => 3618342],
                ['party' => '国民民主党', 'single' => 2, 'proportional' => 3, 'votes' => 3159657],
                ['party' => 'れいわ新選組', 'single' => 0, 'proportional' => 3, 'votes' => 2319156],
                ['party' => '参政党', 'single' => 0, 'proportional' => 1, 'votes' => 1768385],
                ['party' => 'NHK党', 'single' => 0, 'proportional' => 1, 'votes' => 1253872],
            ];

            $this->createElectionResults($election2022, $results2022);
        }
    }

    /**
     * 選挙結果を作成
     */
    private function createElectionResults(Election $election, array $results): void
    {
        // 比例代表の選挙区を取得
        $proportionalDistrict = ElectionDistrict::where('house_type', $election->type)
            ->where('type', 'proportional')
            ->first();

        // 小選挙区のサンプル選挙区を取得
        $singleDistrict = ElectionDistrict::where('house_type', $election->type)
            ->where('type', 'single_seat')
            ->first();

        foreach ($results as $result) {
            $party = PoliticalParty::where('name', $result['party'])->first();
            if (!$party) continue;

            // 小選挙区結果（サマリー）
            if ($result['single'] > 0 && $singleDistrict) {
                ElectionResult::firstOrCreate([
                    'election_id' => $election->id,
                    'district_id' => $singleDistrict->id,
                    'party_id' => $party->id,
                ], [
                    'votes' => (int)($result['votes'] * 0.6), // 概算
                    'seats_won' => $result['single'],
                    'is_winner' => $result['single'] > 0,
                ]);
            }

            // 比例代表結果
            if ($result['proportional'] > 0 && $proportionalDistrict) {
                ElectionResult::firstOrCreate([
                    'election_id' => $election->id,
                    'district_id' => $proportionalDistrict->id,
                    'party_id' => $party->id,
                ], [
                    'votes' => (int)($result['votes'] * 0.4), // 概算
                    'seats_won' => $result['proportional'],
                    'is_winner' => $result['proportional'] > 0,
                ]);
            }
        }
    }

    /**
     * 世論調査データの投入
     */
    private function seedPollData(): void
    {
        $sources = ['NHK', '読売新聞', '朝日新聞', '毎日新聞', '共同通信', '時事通信'];

        // 2024年の世論調査データ例
        $pollDataSamples = [
            ['party' => '自由民主党', 'rates' => [32.5, 31.8, 30.2, 28.5, 26.3]],
            ['party' => '立憲民主党', 'rates' => [8.5, 9.2, 10.5, 12.3, 14.8]],
            ['party' => '日本維新の会', 'rates' => [7.2, 7.5, 7.8, 7.2, 6.5]],
            ['party' => '公明党', 'rates' => [4.2, 4.5, 4.3, 4.1, 3.8]],
            ['party' => '国民民主党', 'rates' => [2.1, 2.5, 3.2, 4.5, 5.8]],
            ['party' => '日本共産党', 'rates' => [3.8, 3.5, 3.2, 3.5, 3.2]],
            ['party' => 'れいわ新選組', 'rates' => [2.5, 2.8, 3.2, 3.5, 4.2]],
        ];

        $startDate = Carbon::create(2024, 6, 1);

        foreach ($pollDataSamples as $sample) {
            $party = PoliticalParty::where('name', $sample['party'])->first();
            if (!$party) continue;

            foreach ($sample['rates'] as $index => $rate) {
                $surveyDate = $startDate->copy()->addMonths($index);
                $source = $sources[$index % count($sources)];

                PollData::firstOrCreate([
                    'party_id' => $party->id,
                    'source' => $source,
                    'survey_end_date' => $surveyDate->format('Y-m-d'),
                ], [
                    'poll_type' => 'phone',
                    'survey_start_date' => $surveyDate->copy()->subDays(2)->format('Y-m-d'),
                    'support_rate' => $rate,
                    'margin_of_error' => 2.5,
                    'sample_size' => rand(1000, 2500),
                    'response_rate' => rand(40, 60),
                ]);
            }
        }

        // 2025年1月の最新世論調査
        $latestPolls = [
            ['party' => '自由民主党', 'rate' => 24.5],
            ['party' => '立憲民主党', 'rate' => 16.2],
            ['party' => '日本維新の会', 'rate' => 5.8],
            ['party' => '公明党', 'rate' => 3.5],
            ['party' => '国民民主党', 'rate' => 6.8],
            ['party' => '日本共産党', 'rate' => 3.0],
            ['party' => 'れいわ新選組', 'rate' => 4.5],
            ['party' => '参政党', 'rate' => 1.8],
        ];

        foreach ($latestPolls as $poll) {
            $party = PoliticalParty::where('name', $poll['party'])->first();
            if (!$party) continue;

            foreach (['NHK', '読売新聞', '朝日新聞'] as $source) {
                PollData::firstOrCreate([
                    'party_id' => $party->id,
                    'source' => $source,
                    'survey_end_date' => '2025-01-15',
                ], [
                    'poll_type' => $source === 'NHK' ? 'phone' : 'mixed',
                    'survey_start_date' => '2025-01-12',
                    'support_rate' => $poll['rate'] + (rand(-10, 10) / 10),
                    'margin_of_error' => 2.0,
                    'sample_size' => rand(1500, 3000),
                    'response_rate' => rand(35, 55),
                ]);
            }
        }
    }
}
