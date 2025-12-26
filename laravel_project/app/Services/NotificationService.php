<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Payment;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationCancelled;
use App\Mail\PaymentReceived;
use App\Mail\CheckInReminder;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * 予約確定メールを送信
     */
    public function sendReservationConfirmation(Reservation $reservation): void
    {
        Mail::to($reservation->customer->email)
            ->send(new ReservationConfirmed($reservation));
    }

    /**
     * 予約キャンセルメールを送信
     */
    public function sendReservationCancellation(Reservation $reservation): void
    {
        Mail::to($reservation->customer->email)
            ->send(new ReservationCancelled($reservation));
    }

    /**
     * 支払い確認メールを送信
     */
    public function sendPaymentConfirmation(Payment $payment): void
    {
        Mail::to($payment->reservation->customer->email)
            ->send(new PaymentReceived($payment));
    }

    /**
     * チェックインリマインダーメールを送信
     */
    public function sendCheckInReminder(Reservation $reservation): void
    {
        Mail::to($reservation->customer->email)
            ->send(new CheckInReminder($reservation));
    }

    /**
     * 予約作成時の通知（仮予約の場合）
     */
    public function sendProvisionalReservationNotification(Reservation $reservation): void
    {
        // 仮予約の場合は、確定待ちのメッセージを送信
        // 実装は ReservationConfirmed と同様ですが、メッセージ内容が異なります
        $this->sendReservationConfirmation($reservation);
    }

    /**
     * 明日チェックインの顧客にリマインダーを送信
     */
    public function sendCheckInRemindersForTomorrow(): int
    {
        $tomorrow = now()->addDay()->startOfDay();
        $reservations = Reservation::where('check_in_date', $tomorrow)
            ->where('status', Reservation::STATUS_CONFIRMED)
            ->get();

        $count = 0;
        foreach ($reservations as $reservation) {
            $this->sendCheckInReminder($reservation);
            $count++;
        }

        return $count;
    }
}
