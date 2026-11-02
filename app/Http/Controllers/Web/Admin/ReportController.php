<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReportIndexRequest;
use App\Http\Requests\Report\UpdateReportStatusRequest;
use App\Models\Report;
use App\Services\Report\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    /**
     * Display a listing of reports.
     */
    public function index(AdminReportIndexRequest $request): View
    {
        $reports = $this->reportService->getAllReports(
            status: $request->validated('status'),
            search: $request->validated('search'),
            dateFrom: $request->validated('date_from'),
            dateTo: $request->validated('date_to'),
            perPage: 10
        );

        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Display the specified report details.
     */
    public function show(Report $report): View
    {
        $report->load('user');

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Update status and notes for the report.
     */
    public function updateStatus(UpdateReportStatusRequest $request, Report $report): RedirectResponse
    {
        $this->reportService->updateReportStatus(
            report: $report,
            status: $request->input('status'),
            adminNotes: $request->input('admin_notes')
        );

        return redirect()->route('admin.reports.show', $report)
            ->with('success', 'Status laporan berhasil diperbarui.');
    }

    /**
     * Delete report and image.
     */
    public function destroy(Report $report): RedirectResponse
    {
        $this->reportService->deleteReport($report);

        return redirect()->route('admin.reports.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }
}
