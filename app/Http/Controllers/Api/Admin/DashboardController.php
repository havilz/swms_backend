<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $recentReports = collect($this->reportService->getRecentReports())
            ->map(fn ($report): array => (new ReportResource($report))->resolve($request))
            ->all();

        return response()->json([
            'data' => [
                'stats' => $this->reportService->getDashboardStats(),
                'recent_reports' => $recentReports,
            ],
        ]);
    }
}
