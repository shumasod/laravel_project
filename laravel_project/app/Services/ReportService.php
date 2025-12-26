<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * 予約統計レポート
     */
    public function getReservationStats(int $accommodationId = null, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        $query = Reservation::whereBetween('created_at', [$startDate, $endDate]);

        if ($accommodationId) {
            $query->whereHas('room', function ($q) use ($accommodationId) {
                $q->where('accommodation_id', $accommodationId);
            });
        }

        $totalReservations = $query->count();
        $confirmedReservations = (clone $query)->where('status', Reservation::STATUS_CONFIRMED)->count();
        $cancelledReservations = (clone $query)->where('status', Reservation::STATUS_CANCELLED)->count();
        $completedReservations = (clone $query)->where('status', Reservation::STATUS_CHECKED_OUT)->count();

        $cancellationRate = $totalReservations > 0
            ? round(($cancelledReservations / $totalReservations) * 100, 2)
            : 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'total_reservations' => $totalReservations,
            'confirmed' => $confirmedReservations,
            'cancelled' => $cancelledReservations,
            'completed' => $completedReservations,
            'cancellation_rate' => $cancellationRate,
        ];
    }

    /**
     * 売上レポート
     */
    public function getRevenueReport(int $accommodationId = null, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        $query = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate]);

        if ($accommodationId) {
            $query->whereHas('reservation.room', function ($q) use ($accommodationId) {
                $q->where('accommodation_id', $accommodationId);
            });
        }

        $totalRevenue = $query->sum('amount');
        $totalTransactions = $query->count();
        $averageTransactionValue = $totalTransactions > 0
            ? round($totalRevenue / $totalTransactions, 2)
            : 0;

        // 支払い方法別の内訳
        $paymentMethodBreakdown = (clone $query)
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->payment_method => $item->total];
            });

        // 日別売上
        $dailyRevenue = (clone $query)
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'average_transaction_value' => $averageTransactionValue,
            'payment_method_breakdown' => $paymentMethodBreakdown,
            'daily_revenue' => $dailyRevenue,
        ];
    }

    /**
     * 占有率レポート
     */
    public function getOccupancyReport(int $accommodationId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        $accommodation = Accommodation::findOrFail($accommodationId);
        $totalRooms = $accommodation->rooms()->count();
        $days = $startDate->diffInDays($endDate);

        // 期間内の予約された部屋数
        $occupiedRoomNights = Reservation::whereHas('room', function ($q) use ($accommodationId) {
            $q->where('accommodation_id', $accommodationId);
        })
            ->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in_date', [$startDate, $endDate])
                    ->orWhereBetween('check_out_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('check_in_date', '<=', $startDate)
                            ->where('check_out_date', '>=', $endDate);
                    });
            })
            ->get()
            ->sum(function ($reservation) {
                return $reservation->getNumberOfNights();
            });

        $totalRoomNights = $totalRooms * $days;
        $occupancyRate = $totalRoomNights > 0
            ? round(($occupiedRoomNights / $totalRoomNights) * 100, 2)
            : 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'total_rooms' => $totalRooms,
            'total_room_nights' => $totalRoomNights,
            'occupied_room_nights' => $occupiedRoomNights,
            'occupancy_rate' => $occupancyRate,
        ];
    }

    /**
     * レビュー統計レポート
     */
    public function getReviewStats(int $accommodationId = null): array
    {
        $query = Review::published();

        if ($accommodationId) {
            $query->where('accommodation_id', $accommodationId);
        }

        $totalReviews = $query->count();
        $averageRating = round($query->avg('overall_rating') ?? 0, 2);

        // 評価別の分布
        $ratingDistribution = (clone $query)
            ->select('overall_rating', DB::raw('COUNT(*) as count'))
            ->groupBy('overall_rating')
            ->orderBy('overall_rating', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->overall_rating => $item->count];
            });

        // 詳細評価の平均
        $detailRatings = [
            'cleanliness' => round($query->avg('cleanliness_rating') ?? 0, 2),
            'service' => round($query->avg('service_rating') ?? 0, 2),
            'location' => round($query->avg('location_rating') ?? 0, 2),
            'value' => round($query->avg('value_rating') ?? 0, 2),
            'amenities' => round($query->avg('amenities_rating') ?? 0, 2),
        ];

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => $averageRating,
            'rating_distribution' => $ratingDistribution,
            'detail_ratings' => $detailRatings,
        ];
    }

    /**
     * 顧客統計レポート
     */
    public function getCustomerStats(int $accommodationId = null): array
    {
        $query = Reservation::where('status', Reservation::STATUS_CHECKED_OUT);

        if ($accommodationId) {
            $query->whereHas('room', function ($q) use ($accommodationId) {
                $q->where('accommodation_id', $accommodationId);
            });
        }

        // リピーター率
        $totalCustomers = $query->distinct('customer_id')->count('customer_id');
        $repeatCustomers = $query
            ->select('customer_id', DB::raw('COUNT(*) as visit_count'))
            ->groupBy('customer_id')
            ->having('visit_count', '>', 1)
            ->get()
            ->count();

        $repeatRate = $totalCustomers > 0
            ? round(($repeatCustomers / $totalCustomers) * 100, 2)
            : 0;

        // 平均宿泊日数
        $averageStayDuration = round($query->get()->avg(function ($reservation) {
            return $reservation->getNumberOfNights();
        }) ?? 0, 2);

        // 平均ゲスト数
        $averageGuestCount = round($query->avg('number_of_guests') ?? 0, 2);

        return [
            'total_customers' => $totalCustomers,
            'repeat_customers' => $repeatCustomers,
            'repeat_rate' => $repeatRate,
            'average_stay_duration' => $averageStayDuration,
            'average_guest_count' => $averageGuestCount,
        ];
    }

    /**
     * ダッシュボード用の総合レポート
     */
    public function getDashboardReport(int $accommodationId = null): array
    {
        return [
            'reservations' => $this->getReservationStats($accommodationId),
            'revenue' => $this->getRevenueReport($accommodationId),
            'occupancy' => $accommodationId ? $this->getOccupancyReport($accommodationId) : null,
            'reviews' => $this->getReviewStats($accommodationId),
            'customers' => $this->getCustomerStats($accommodationId),
        ];
    }
}
