<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\EventController;
use App\Models\Accommodation;
use App\Models\Room;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\PricingRule;
use App\Models\RoomInventory;
use App\Models\Payment;
use App\Models\Review;
use App\Services\ReservationService;
use App\Services\PricingService;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\ReportService;

Route::get('/', function () {
    return view('welcome');
});

// 管理系リソース (認証必須)
Route::middleware('auth')->group(function () {
    Route::resource('accommodations', AccommodationController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('reservations', ReservationController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('reviews', ReviewController::class);

    // ===== 在庫管理システム =====
    Route::resource('products', ProductController::class);
    Route::get('/products/{product}/qrcode', [ProductController::class, 'qrcode'])->name('products.qrcode');
    Route::get('/products/{product}/qrcode/download', [ProductController::class, 'qrcodeDownload'])->name('products.qrcode.download');
    Route::post('/products/{product}/stock-transactions', [StockTransactionController::class, 'store'])->name('stock-transactions.store');
    Route::get('/stock-transactions', [StockTransactionController::class, 'index'])->name('stock-transactions.index');

    // 決済関連のカスタムルート
    Route::post('/payments/{payment}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

    // レビュー関連のカスタムルート
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'addHelpfulVote'])->name('reviews.helpful');
    Route::post('/reviews/{review}/admin-response', [ReviewController::class, 'addAdminResponse'])->name('reviews.admin-response');
});

// レポート関連のルート (認証必須)
Route::middleware('auth')->group(function () {
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/reports/reservations', [ReportController::class, 'reservations'])->name('reports.reservations');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('/reports/reviews', [ReportController::class, 'reviews'])->name('reports.reviews');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});

// ===== 旅行検索サイト =====

// トップ・検索
Route::get('/travel', [TravelController::class, 'index'])->name('travel.index');
Route::get('/travel/search', [TravelController::class, 'search'])->name('travel.search');
Route::get('/travel/suggest', [TravelController::class, 'suggest'])->name('travel.suggest');

// 施設詳細
Route::get('/travel/accommodations/{id}', [TravelController::class, 'show'])->name('travel.show');
Route::get('/travel/accommodations/{id}/plans', [TravelController::class, 'searchPlans'])->name('travel.plans');
Route::get('/travel/accommodations/{id}/reviews', [TravelController::class, 'reviews'])->name('travel.reviews');

// 予約フロー
Route::get('/travel/booking/{plan}', [TravelController::class, 'booking'])->name('travel.booking');
Route::post('/travel/booking/confirm', [TravelController::class, 'confirmBooking'])->name('travel.booking.confirm');
Route::post('/travel/booking/complete', [TravelController::class, 'completeBooking'])->name('travel.booking.complete');

// お気に入り
Route::post('/travel/favorites', [TravelController::class, 'addFavorite'])->name('travel.favorites.add');
Route::delete('/travel/favorites/{accommodation}', [TravelController::class, 'removeFavorite'])->name('travel.favorites.remove');

// エリアAPI
Route::get('/travel/areas', [TravelController::class, 'areas'])->name('travel.areas');

// ===== イベント検索システム =====

// トップ・検索
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/search', [EventController::class, 'search'])->name('events.search');
Route::get('/events/calendar', [EventController::class, 'calendar'])->name('events.calendar');

// イベント詳細
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');

// お気に入り
Route::get('/events/my/favorites', [EventController::class, 'favorites'])->name('events.favorites');
Route::post('/events/favorites', [EventController::class, 'addFavorite'])->name('events.favorites.add');
Route::delete('/events/favorites/{eventId}', [EventController::class, 'removeFavorite'])->name('events.favorites.remove');

// API
Route::get('/api/events/search', [EventController::class, 'apiSearch'])->name('events.api.search');

// ===== 選挙分析システム =====

// 公開読み取り (レート制限付き)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/elections/dashboard', [ElectionController::class, 'dashboard'])->name('elections.dashboard');
    Route::get('/elections', [ElectionController::class, 'index'])->name('elections.index');
    Route::get('/elections/{election}', [ElectionController::class, 'show'])->name('elections.show');
    Route::get('/elections/{election}/predictions', [ElectionController::class, 'getPredictions'])->name('elections.predictions');
    Route::get('/elections/{election}/validate-accuracy', [ElectionController::class, 'validateAccuracy'])->name('elections.validate-accuracy');
    Route::get('/elections-compare', [ElectionController::class, 'compare'])->name('elections.compare');
    Route::get('/poll-data', [ElectionController::class, 'pollData'])->name('poll-data.index');
    Route::get('/parties', [ElectionController::class, 'parties'])->name('parties.index');
    Route::get('/parties/{party}/trend', [ElectionController::class, 'partyTrend'])->name('parties.trend');
    Route::get('/election-districts', [ElectionController::class, 'districts'])->name('districts.index');
    Route::get('/elections/{election}/export', [ElectionController::class, 'exportReport'])->name('elections.export');
});

// 書き込み操作 (認証・管理者権限・レート制限必須)
Route::middleware(['auth', 'can:admin', 'throttle:20,1'])->group(function () {
    Route::post('/elections', [ElectionController::class, 'store'])->name('elections.store');
    Route::post('/elections/{election}/predict', [ElectionController::class, 'predict'])->name('elections.predict');
    Route::post('/elections/{election}/results', [ElectionController::class, 'storeResult'])->name('elections.results.store');
    Route::post('/poll-data', [ElectionController::class, 'storePollData'])->name('poll-data.store');
    Route::post('/parties', [ElectionController::class, 'storeParty'])->name('parties.store');
    Route::post('/elections/import-csv', [ElectionController::class, 'importCsv'])->name('elections.import-csv');
});

// ===== テスト用ルート (local環境のみ) =====
if (!app()->environment('local', 'testing')) {
    return;
}

// データベース状態確認
Route::get('/test-db-status', function () {
    return response()->json([
        'accommodations' => Accommodation::count(),
        'rooms' => Room::count(),
        'customers' => Customer::count(),
        'reservations' => Reservation::count(),
        'pricing_rules' => PricingRule::count(),
        'room_inventories' => RoomInventory::count(),
    ]);
});

// サンプルデータ作成
Route::get('/test-create-sample-data', function (InventoryService $inventoryService) {
    try {
        // 宿泊施設を作成
        $accommodation = Accommodation::firstOrCreate(
            ['name' => 'サンプルホテル東京'],
            [
                'address' => '東京都渋谷区1-2-3',
                'phone' => '03-1234-5678',
                'email' => 'info@sample-hotel.com',
                'description' => '快適なホテルです'
            ]
        );

        // 部屋を作成
        $room = Room::firstOrCreate(
            ['accommodation_id' => $accommodation->id, 'room_number' => '101'],
            [
                'room_type' => 'standard',
                'price_per_night' => 10000,
                'capacity' => 2,
                'description' => 'スタンダードルーム',
                'is_available' => true
            ]
        );

        // 顧客を作成
        $customer = Customer::firstOrCreate(
            ['email' => 'yamada@example.com'],
            [
                'name' => '山田太郎',
                'phone' => '090-1234-5678',
                'address' => '東京都新宿区',
                'privacy_consent' => true,
                'privacy_consent_date' => now(),
            ]
        );

        // 在庫を初期化（既に存在する場合はスキップ）
        $existingInventory = RoomInventory::where('accommodation_id', $accommodation->id)
            ->where('room_type', 'standard')
            ->count();

        if ($existingInventory === 0) {
            $inventoryService->initializeInventory(
                $accommodation->id,
                'standard',
                now()->addDays(1),
                now()->addDays(30),
                10
            );
        }

        // 料金ルールを作成
        PricingRule::firstOrCreate(
            [
                'accommodation_id' => $accommodation->id,
                'rule_type' => 'day_of_week',
                'name' => '週末料金'
            ],
            [
                'room_type' => 'standard',
                'description' => '金曜日と土曜日は20%増',
                'conditions' => ['days' => ['friday', 'saturday']],
                'calculation_type' => 'percentage',
                'value' => 20,
                'priority' => 10,
                'is_active' => true,
            ]
        );

        PricingRule::firstOrCreate(
            [
                'accommodation_id' => $accommodation->id,
                'rule_type' => 'consecutive_nights',
                'name' => '3泊以上割引'
            ],
            [
                'room_type' => 'standard',
                'description' => '3泊以上で10%オフ',
                'conditions' => ['min_nights' => 3],
                'calculation_type' => 'percentage',
                'value' => 10,
                'priority' => 5,
                'is_active' => true,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'サンプルデータを作成しました',
            'data' => [
                'accommodation' => $accommodation,
                'room' => $room,
                'customer' => $customer,
                'inventory_count' => RoomInventory::where('accommodation_id', $accommodation->id)->count(),
                'pricing_rules_count' => PricingRule::where('accommodation_id', $accommodation->id)->count(),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// 予約作成テスト
Route::get('/test-create-reservation', function (ReservationService $service) {
    try {
        $room = Room::first();
        $customer = Customer::first();

        if (!$room || !$customer) {
            return response()->json([
                'error' => 'データが見つかりません。先に /test-create-sample-data を実行してください。'
            ], 400);
        }

        $reservation = $service->createReservation([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'check_in_date' => now()->addDays(7)->format('Y-m-d'),
            'check_out_date' => now()->addDays(9)->format('Y-m-d'),
            'number_of_guests' => 2,
        ]);

        return response()->json([
            'status' => 'success',
            'reservation' => [
                'id' => $reservation->id,
                'status' => $reservation->status,
                'check_in' => $reservation->check_in_date->format('Y-m-d'),
                'check_out' => $reservation->check_out_date->format('Y-m-d'),
                'nights' => $reservation->getNumberOfNights(),
                'total_amount' => '¥' . number_format($reservation->total_amount),
            ],
            'pricing_breakdown' => $reservation->price_breakdown,
            'applied_discounts' => $reservation->applied_discounts,
            'customer' => $reservation->customer->name,
            'room' => $reservation->room->room_number,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// 料金計算テスト
Route::get('/test-pricing', function (PricingService $pricingService) {
    try {
        $room = Room::first();

        if (!$room) {
            return response()->json([
                'error' => 'データが見つかりません。先に /test-create-sample-data を実行してください。'
            ], 400);
        }

        // 次の金曜日から2泊（週末料金が適用されるか確認）
        $checkIn = \Carbon\Carbon::parse('next friday');
        $checkOut = $checkIn->copy()->addDays(2);

        $pricing = $pricingService->calculateTotalPrice(
            $room,
            $checkIn,
            $checkOut,
            2
        );

        return response()->json([
            'status' => 'success',
            'check_in' => $checkIn->format('Y-m-d (l)'),
            'check_out' => $checkOut->format('Y-m-d (l)'),
            'nights' => $pricing['nights'],
            'base_amount' => '¥' . number_format($pricing['base_amount']),
            'total_amount' => '¥' . number_format($pricing['total_amount']),
            'breakdown' => $pricing['breakdown'],
            'applied_discounts' => $pricing['applied_discounts'],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// 在庫状況テスト
Route::get('/test-inventory', function (InventoryService $inventoryService) {
    try {
        $accommodation = Accommodation::first();

        if (!$accommodation) {
            return response()->json([
                'error' => 'データが見つかりません。先に /test-create-sample-data を実行してください。'
            ], 400);
        }

        $status = $inventoryService->getInventoryStatus(
            $accommodation->id,
            'standard',
            now()->addDays(1),
            now()->addDays(10)
        );

        return response()->json([
            'status' => 'success',
            'accommodation' => $accommodation->name,
            'room_type' => 'standard',
            'inventory' => $status,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// ステータス遷移テスト
Route::get('/test-status-transition', function () {
    try {
        $reservation = Reservation::where('status', 'provisional')->first();

        if (!$reservation) {
            return response()->json([
                'error' => '仮予約が見つかりません。先に /test-create-reservation を実行してください。'
            ], 400);
        }

        $history = [];

        // 確定
        $reservation->changeStatus('confirmed', null, '予約確定');
        $history[] = 'provisional → confirmed';

        // チェックイン
        $reservation->checkIn();
        $history[] = 'confirmed → checked_in';

        // チェックアウト
        $reservation->checkOut();
        $history[] = 'checked_in → checked_out';

        return response()->json([
            'status' => 'success',
            'reservation_id' => $reservation->id,
            'current_status' => $reservation->status,
            'actual_check_in_time' => $reservation->actual_check_in_time,
            'actual_check_out_time' => $reservation->actual_check_out_time,
            'history' => $history,
            'status_histories' => $reservation->statusHistories->map(function ($h) {
                return [
                    'from' => $h->from_status,
                    'to' => $h->to_status,
                    'time' => $h->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// ===== 新機能のテスト用ルート =====

// 決済テスト
Route::get('/test-payment', function (PaymentService $paymentService) {
    try {
        $reservation = Reservation::first();

        if (!$reservation) {
            return response()->json([
                'error' => 'データが見つかりません。先に /test-create-sample-data を実行してください。'
            ], 400);
        }

        // 決済を作成
        $payment = $paymentService->createPayment([
            'reservation_id' => $reservation->id,
            'amount' => $reservation->total_amount,
            'payment_method' => 'credit_card',
            'payment_gateway' => 'stripe',
        ]);

        // 決済を処理
        $paymentService->processPayment($payment);

        return response()->json([
            'status' => 'success',
            'payment' => [
                'id' => $payment->id,
                'amount' => '¥' . number_format($payment->amount),
                'method' => $payment->payment_method,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
            ],
            'reservation' => [
                'id' => $reservation->id,
                'payment_status' => $reservation->payment_status,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// 返金テスト
Route::get('/test-refund', function (PaymentService $paymentService) {
    try {
        $payment = Payment::where('status', 'completed')->first();

        if (!$payment) {
            return response()->json([
                'error' => '決済が見つかりません。先に /test-payment を実行してください。'
            ], 400);
        }

        // 全額返金
        $paymentService->refundPayment($payment, null, 'テスト返金');

        return response()->json([
            'status' => 'success',
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
                'refund_amount' => '¥' . number_format($payment->refund_amount),
                'refund_reason' => $payment->refund_reason,
                'refunded_at' => $payment->refunded_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// レビューテスト
Route::get('/test-review', function () {
    try {
        $reservation = Reservation::where('status', Reservation::STATUS_CHECKED_OUT)->first();

        if (!$reservation) {
            // チェックアウト済みの予約がない場合、テスト用に作成
            $reservation = Reservation::first();
            if ($reservation) {
                $reservation->update(['status' => Reservation::STATUS_CHECKED_OUT]);
            } else {
                return response()->json([
                    'error' => 'データが見つかりません。先に /test-create-sample-data を実行してください。'
                ], 400);
            }
        }

        // 既存のレビューがあればスキップ
        $existingReview = Review::where('reservation_id', $reservation->id)->first();
        if ($existingReview) {
            return response()->json([
                'status' => 'success',
                'message' => '既にレビューが存在します',
                'review' => [
                    'id' => $existingReview->id,
                    'overall_rating' => $existingReview->overall_rating,
                    'title' => $existingReview->title,
                    'comment' => $existingReview->comment,
                ],
            ]);
        }

        // レビューを作成
        $review = Review::create([
            'reservation_id' => $reservation->id,
            'customer_id' => $reservation->customer_id,
            'accommodation_id' => $reservation->room->accommodation_id,
            'overall_rating' => 5,
            'cleanliness_rating' => 5,
            'service_rating' => 4,
            'location_rating' => 5,
            'value_rating' => 4,
            'amenities_rating' => 5,
            'title' => '素晴らしい滞在でした！',
            'comment' => 'スタッフの対応が素晴らしく、部屋もとても清潔でした。また利用したいと思います。',
            'is_verified' => true,
            'is_published' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'review' => [
                'id' => $review->id,
                'overall_rating' => $review->overall_rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'is_verified' => $review->is_verified,
                'is_published' => $review->is_published,
            ],
            'reservation' => [
                'id' => $reservation->id,
                'customer' => $reservation->customer->name,
                'accommodation' => $reservation->room->accommodation->name,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// レポートテスト
Route::get('/test-report', function (ReportService $reportService) {
    try {
        $accommodation = Accommodation::first();

        if (!$accommodation) {
            return response()->json([
                'error' => 'データが見つかりません。先に /test-create-sample-data を実行してください。'
            ], 400);
        }

        $report = $reportService->getDashboardReport($accommodation->id);

        return response()->json([
            'status' => 'success',
            'accommodation' => $accommodation->name,
            'report' => $report,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// メール通知テスト
Route::get('/test-notification', function (NotificationService $notificationService) {
    try {
        return response()->json([
            'status' => 'success',
            'message' => 'メール通知機能は実装済みです。',
            'note' => '実際のメール送信には .env でメール設定が必要です。',
            'available_notifications' => [
                'sendReservationConfirmation' => '予約確定メール',
                'sendReservationCancellation' => 'キャンセルメール',
                'sendPaymentConfirmation' => '支払い確認メール',
                'sendCheckInReminder' => 'チェックインリマインダー',
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// ===== 選挙分析テスト用ルート =====

use App\Models\Election;
use App\Models\PoliticalParty;
use App\Models\PollData;
use App\Services\ElectionDataService;
use App\Services\ElectionAnalysisService;

// 選挙データ状態確認
Route::get('/test-election-status', function () {
    return response()->json([
        'status' => 'success',
        'data' => [
            'elections' => Election::count(),
            'parties' => PoliticalParty::count(),
            'poll_data' => PollData::count(),
            'hr_elections' => Election::where('type', 'house_of_representatives')->count(),
            'hc_elections' => Election::where('type', 'house_of_councillors')->count(),
        ],
    ]);
});

// 選挙シードデータ作成
Route::get('/test-seed-elections', function () {
    try {
        $seeder = new \Database\Seeders\ElectionSeeder();
        $seeder->run();

        return response()->json([
            'status' => 'success',
            'message' => '選挙データをシードしました',
            'data' => [
                'elections' => Election::count(),
                'parties' => PoliticalParty::count(),
                'poll_data' => PollData::count(),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// 議席予測テスト
Route::get('/test-election-prediction', function (ElectionAnalysisService $analysisService) {
    try {
        $election = Election::orderBy('election_date', 'desc')->first();

        if (!$election) {
            return response()->json([
                'error' => '選挙データがありません。先に /test-seed-elections を実行してください。'
            ], 400);
        }

        $prediction = $analysisService->predictSeats($election->id);

        return response()->json([
            'status' => 'success',
            'election' => $election->name,
            'predictions' => $prediction['predictions'],
            'total_seats' => $prediction['total_seats'],
            'methodology' => $prediction['methodology'],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// 選挙比較テスト
Route::get('/test-election-compare', function (ElectionAnalysisService $analysisService) {
    try {
        $elections = Election::where('type', 'house_of_representatives')
            ->orderBy('election_date', 'desc')
            ->take(2)
            ->get();

        if ($elections->count() < 2) {
            return response()->json([
                'error' => '比較する選挙が2つ以上必要です。'
            ], 400);
        }

        $comparison = $analysisService->compareElections(
            $elections[1]->id,
            $elections[0]->id
        );

        return response()->json([
            'status' => 'success',
            'comparison' => $comparison,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// 政党トレンド分析テスト
Route::get('/test-party-trend', function (ElectionAnalysisService $analysisService) {
    try {
        $party = PoliticalParty::where('name', '自由民主党')->first();

        if (!$party) {
            return response()->json([
                'error' => '政党データがありません。先に /test-seed-elections を実行してください。'
            ], 400);
        }

        $trends = $analysisService->analyzePollTrends(
            $party->id,
            \Carbon\Carbon::now()->subYears(2),
            \Carbon\Carbon::now()
        );

        return response()->json([
            'status' => 'success',
            'party' => $party->name,
            'trends' => $trends,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
