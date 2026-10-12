<?php

// ===== 新機能のテスト用ルート =====
// このファイルのルートを web.php に追加してください
// IMPORTANT: This file must only be included in local/testing environments.
// These routes create and modify real database records (payments, refunds, reviews).

if (!app()->environment('local', 'testing')) {
    return;
}

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
