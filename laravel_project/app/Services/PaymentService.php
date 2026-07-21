<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * 決済を作成
     */
    public function createPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::create([
                'reservation_id' => $data['reservation_id'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_gateway' => $data['payment_gateway'] ?? null,
                'payment_details' => $data['payment_details'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $payment;
        });
    }

    /**
     * 決済処理を実行
     */
    public function processPayment(Payment $payment, array $paymentData = []): Payment
    {
        return DB::transaction(function () use ($payment, $paymentData) {
            // ステータスを処理中に変更
            $payment->status = 'processing';
            $payment->save();

            try {
                // 実際の決済ゲートウェイとの連携処理
                // （Stripe、PayPalなどのAPIを呼び出す）
                $result = $this->callPaymentGateway($payment, $paymentData);

                if ($result['success']) {
                    $payment->markAsPaid($result['transaction_id']);

                    // 予約のステータスも更新
                    $payment->reservation->update([
                        'payment_status' => 'paid',
                    ]);
                } else {
                    $payment->markAsFailed($result['error'] ?? 'Unknown error');
                }

                return $payment;
            } catch (Exception $e) {
                $payment->markAsFailed($e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * 決済ゲートウェイへの接続（モック実装）
     * 実際の実装では、Stripe、PayPalなどのSDKを使用
     */
    protected function callPaymentGateway(Payment $payment, array $paymentData): array
    {
        // モック実装: 実際はStripe、PayPalなどのAPIを呼び出す
        // 例: Stripe::charge(['amount' => $payment->amount, ...])

        // テスト用に常に成功を返す
        return [
            'success' => true,
            'transaction_id' => 'TXN_' . strtoupper(uniqid()),
        ];

        // 実際の実装例（Stripeの場合）:
        // try {
        //     $charge = \Stripe\Charge::create([
        //         'amount' => $payment->amount * 100, // cents
        //         'currency' => 'jpy',
        //         'source' => $paymentData['stripe_token'],
        //         'description' => 'Reservation #' . $payment->reservation_id,
        //     ]);
        //
        //     return [
        //         'success' => true,
        //         'transaction_id' => $charge->id,
        //     ];
        // } catch (\Stripe\Exception\CardException $e) {
        //     return [
        //         'success' => false,
        //         'error' => $e->getMessage(),
        //     ];
        // }
    }

    /**
     * 返金処理
     */
    public function refundPayment(Payment $payment, float $amount = null, string $reason = null): Payment
    {
        if (!$payment->isRefundable() && !$payment->isPartiallyRefundable()) {
            throw new Exception('この決済は返金できません。');
        }

        $refundAmount = $amount ?? $payment->amount;

        if ($refundAmount > $payment->amount) {
            throw new Exception('返金額が決済額を超えています。');
        }

        return DB::transaction(function () use ($payment, $refundAmount, $reason) {
            // 実際の返金処理（決済ゲートウェイへの連携）
            $result = $this->callRefundGateway($payment, $refundAmount);

            if ($result['success']) {
                $payment->refund($refundAmount, $reason);

                // 予約のステータスも更新
                $payment->reservation->update([
                    'payment_status' => 'refunded',
                ]);
            } else {
                throw new Exception($result['error'] ?? 'Refund failed');
            }

            return $payment;
        });
    }

    /**
     * 返金ゲートウェイへの接続（モック実装）
     */
    protected function callRefundGateway(Payment $payment, float $amount): array
    {
        // モック実装: 実際はStripe、PayPalなどのAPIを呼び出す
        return [
            'success' => true,
        ];

        // 実際の実装例（Stripeの場合）:
        // try {
        //     $refund = \Stripe\Refund::create([
        //         'charge' => $payment->transaction_id,
        //         'amount' => $amount * 100, // cents
        //     ]);
        //
        //     return ['success' => true];
        // } catch (\Stripe\Exception\ApiErrorException $e) {
        //     return [
        //         'success' => false,
        //         'error' => $e->getMessage(),
        //     ];
        // }
    }

    /**
     * 決済キャンセル
     */
    public function cancelPayment(Payment $payment): Payment
    {
        if (!$payment->isCancellable()) {
            throw new Exception('この決済はキャンセルできません。');
        }

        $payment->status = 'cancelled';
        $payment->save();

        return $payment;
    }

    /**
     * 予約の支払い状況を確認
     */
    public function checkPaymentStatus(Reservation $reservation): array
    {
        $payments = $reservation->payments;
        $totalPaid = $payments->where('status', 'completed')->sum('amount');
        $totalRefunded = $payments->where('status', 'refunded')->sum('refund_amount');
        $pendingAmount = $reservation->total_amount - $totalPaid + $totalRefunded;

        return [
            'total_amount' => $reservation->total_amount,
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'pending_amount' => $pendingAmount,
            'is_fully_paid' => $pendingAmount <= 0,
            'payments' => $payments,
        ];
    }
}
