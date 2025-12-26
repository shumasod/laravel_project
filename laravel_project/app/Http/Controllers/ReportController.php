<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $accommodationId = $request->input('accommodation_id');
        $report = $this->reportService->getDashboardReport($accommodationId);

        return view('reports.dashboard', compact('report', 'accommodationId'));
    }

    /**
     * 予約レポート
     */
    public function reservations(Request $request)
    {
        $accommodationId = $request->input('accommodation_id');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $stats = $this->reportService->getReservationStats($accommodationId, $startDate, $endDate);

        return view('reports.reservations', compact('stats', 'accommodationId'));
    }

    /**
     * 売上レポート
     */
    public function revenue(Request $request)
    {
        $accommodationId = $request->input('accommodation_id');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $report = $this->reportService->getRevenueReport($accommodationId, $startDate, $endDate);

        return view('reports.revenue', compact('report', 'accommodationId'));
    }

    /**
     * 占有率レポート
     */
    public function occupancy(Request $request)
    {
        $accommodationId = $request->input('accommodation_id');

        if (!$accommodationId) {
            return redirect()->back()->withErrors(['error' => '宿泊施設を選択してください。']);
        }

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $report = $this->reportService->getOccupancyReport($accommodationId, $startDate, $endDate);

        return view('reports.occupancy', compact('report', 'accommodationId'));
    }

    /**
     * レビューレポート
     */
    public function reviews(Request $request)
    {
        $accommodationId = $request->input('accommodation_id');
        $stats = $this->reportService->getReviewStats($accommodationId);

        return view('reports.reviews', compact('stats', 'accommodationId'));
    }

    /**
     * 顧客レポート
     */
    public function customers(Request $request)
    {
        $accommodationId = $request->input('accommodation_id');
        $stats = $this->reportService->getCustomerStats($accommodationId);

        return view('reports.customers', compact('stats', 'accommodationId'));
    }

    /**
     * レポートをJSON形式でエクスポート
     */
    public function export(Request $request)
    {
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
