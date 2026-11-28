<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Inertia\Inertia;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * ダッシュボード
     */
    public function dashboard(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'accommodation_id' => 'nullable|integer|exists:accommodations,id',
        ]);

        $accommodationId = $request->input('accommodation_id');
        $report = $this->reportService->getDashboardReport($accommodationId);

        return Inertia::render('Reports/Dashboard', [
            'report' => $report,
            'accommodationId' => $accommodationId,
        ]);
    }

    /**
     * 予約レポート
     */
    public function reservations(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'accommodation_id' => 'nullable|integer|exists:accommodations,id',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);

        $accommodationId = $request->input('accommodation_id');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $stats = $this->reportService->getReservationStats($accommodationId, $startDate, $endDate);

        return Inertia::render('Reports/Reservations', [
            'stats' => $stats,
            'accommodationId' => $accommodationId,
        ]);
    }

    /**
     * 売上レポート
     */
    public function revenue(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'accommodation_id' => 'nullable|integer|exists:accommodations,id',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);

        $accommodationId = $request->input('accommodation_id');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $report = $this->reportService->getRevenueReport($accommodationId, $startDate, $endDate);

        return Inertia::render('Reports/Revenue', [
            'report' => $report,
            'accommodationId' => $accommodationId,
        ]);
    }

    /**
     * 占有率レポート
     */
    public function occupancy(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'accommodation_id' => 'required|integer|exists:accommodations,id',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);

        $accommodationId = $request->input('accommodation_id');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $report = $this->reportService->getOccupancyReport($accommodationId, $startDate, $endDate);

        return Inertia::render('Reports/Occupancy', [
            'report' => $report,
            'accommodationId' => $accommodationId,
        ]);
    }

    /**
     * レビューレポート
     */
    public function reviews(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'accommodation_id' => 'nullable|integer|exists:accommodations,id',
        ]);

        $accommodationId = $request->input('accommodation_id');
        $stats = $this->reportService->getReviewStats($accommodationId);

        return Inertia::render('Reports/Reviews', [
            'stats' => $stats,
            'accommodationId' => $accommodationId,
        ]);
    }

    /**
     * 顧客レポート
     */
    public function customers(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'accommodation_id' => 'nullable|integer|exists:accommodations,id',
        ]);

        $accommodationId = $request->input('accommodation_id');
        $stats = $this->reportService->getCustomerStats($accommodationId);

        return Inertia::render('Reports/Customers', [
            'stats' => $stats,
            'accommodationId' => $accommodationId,
        ]);
    }

    /**
     * レポートをJSON形式でエクスポート
     */
    public function export(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'accommodation_id' => 'nullable|integer|exists:accommodations,id',
            'type'             => 'nullable|in:dashboard,reservations,revenue,occupancy,reviews,customers',
        ]);

        $type = $request->input('type', 'dashboard');
        $accommodationId = $request->input('accommodation_id');

        $data = match ($type) {
            'reservations' => $this->reportService->getReservationStats($accommodationId),
            'revenue' => $this->reportService->getRevenueReport($accommodationId),
            'occupancy' => $accommodationId ? $this->reportService->getOccupancyReport($accommodationId) : null,
            'reviews' => $this->reportService->getReviewStats($accommodationId),
            'customers' => $this->reportService->getCustomerStats($accommodationId),
            default => $this->reportService->getDashboardReport($accommodationId),
        };

        return response()->json($data);
    }
}
