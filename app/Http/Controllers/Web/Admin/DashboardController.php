<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    /**
     * Display dashboard summary.
     */
    public function index(): View
    {
        $stats = $this->reportService->getDashboardStats();
        $recentReports = $this->reportService->getRecentReports();

        return view('admin.dashboard', compact('stats', 'recentReports'));
    }
}
