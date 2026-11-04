<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiReportIndexRequest;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Requests\Report\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    /**
     * Get reports submitted by the authenticated mobile user.
     */
    public function index(ApiReportIndexRequest $request): JsonResponse
    {
        $reports = $this->reportService->getReportsForUser(
            userId: $request->user()->id,
            status: $request->validated('status'),
            search: $request->validated('search'),
            perPage: (int) ($request->validated('per_page') ?? 10)
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

    /**
     * Store a new report submitted by user.
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = $this->reportService->createReport(
            user: $request->user(),
            data: $request->validated(),
            image: $request->file('image')
        );

        return response()->json([
            'message' => 'Laporan berhasil dibuat.',
            'data' => (new ReportResource($report))->resolve($request),
        ], 201);
    }

    /**
     * Get a specific report detail (only if owned by the user).
     */
    public function show(Request $request, Report $report): JsonResponse
    {
        if ($report->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk melihat laporan ini.',
            ], 403);
        }

        return response()->json([
            'data' => (new ReportResource($report))->resolve($request),
        ]);
    }

    /**
     * Update user-editable fields on a report owned by the authenticated user.
     */
    public function update(UpdateReportRequest $request, Report $report): JsonResponse
    {
        $report = $this->reportService->updateReport(
            report: $report,
            data: $request->validated(),
            image: $request->file('image')
        );

        return response()->json([
            'message' => 'Laporan berhasil diperbarui.',
            'data' => (new ReportResource($report))->resolve($request),
        ]);
    }
}
