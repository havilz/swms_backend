<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReportIndexRequest;
use App\Http\Requests\Report\UpdateReportStatusRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\Report\ModelClassificationException;
use App\Services\Report\ReportService;
use App\Services\Report\WasteModelClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function index(AdminReportIndexRequest $request): JsonResponse
    {
        $reports = $this->reportService->getAllReports(
            status: $request->validated('status'),
            search: $request->validated('search'),
            dateFrom: $request->validated('date_from'),
            dateTo: $request->validated('date_to'),
            perPage: (int) ($request->validated('per_page') ?? 15),
        );

        return response()->json([
            'data' => collect($reports->items())
                ->map(fn (Report $report): array => (new ReportResource($report))->resolve($request))
                ->all(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function show(Request $request, Report $report): JsonResponse
    {
        $report->load('user');

        return response()->json([
            'data' => (new ReportResource($report))->resolve($request),
        ]);
    }

    public function classify(Report $report, WasteModelClient $wasteModelClient): JsonResponse
    {
        try {
            return response()->json($wasteModelClient->classify($report));
        } catch (ModelClassificationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->status);
        }
    }

    public function updateStatus(UpdateReportStatusRequest $request, Report $report): JsonResponse
    {
        $report = $this->reportService->updateReportStatus(
            report: $report,
            status: $request->validated('status'),
            adminNotes: $request->validated('admin_notes'),
        );

        return response()->json([
            'message' => 'Status laporan berhasil diperbarui.',
            'data' => (new ReportResource($report))->resolve($request),
        ]);
    }

    public function destroy(Report $report): JsonResponse
    {
        $this->reportService->deleteReport($report);

        return response()->json([
            'message' => 'Laporan berhasil dihapus.',
        ]);
    }
}
