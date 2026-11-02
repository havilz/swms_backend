<?php

namespace App\Services\Report;

use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReportService
{
    /**
     * Get paginated reports for a specific regular user (mobile).
     */
    public function getReportsForUser(int $userId, ?string $status = null, ?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Report::where('user_id', $userId)
            ->ofStatus($status)
            ->search($search)
            ->latest()
            ->paginate($this->perPage($perPage))
            ->withQueryString();
    }

    /**
     * Get all reports for administrator with search & filter.
     */
    public function getAllReports(?string $status = null, ?string $search = null, ?string $dateFrom = null, ?string $dateTo = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Report::with('user')
            ->ofStatus($status)
            ->search($search);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->latest()->paginate($this->perPage($perPage))->withQueryString();
    }

    /**
     * Get the most recent reports for the dashboard.
     */
    public function getRecentReports(int $limit = 5): array
    {
        return Report::with('user')->latest()->limit($limit)->get()->all();
    }

    /**
     * Get summary counts for admin dashboard overview.
     *
     * @return array{total_reports: int, pending: int, in_progress: int, completed: int, rejected: int, total_users: int}
     */
    public function getDashboardStats(): array
    {
        return [
            'total_reports' => Report::count(),
            'pending' => Report::where('status', Report::STATUS_PENDING)->count(),
            'in_progress' => Report::where('status', Report::STATUS_IN_PROGRESS)->count(),
            'completed' => Report::where('status', Report::STATUS_COMPLETED)->count(),
            'rejected' => Report::where('status', Report::STATUS_REJECTED)->count(),
            'total_users' => User::where('role', 'user')->count(),
        ];
    }

    /**
     * Create a new waste report with optional image upload.
     */
    public function createReport(User $user, array $data, ?UploadedFile $image = null): Report
    {
        $imagePath = null;
        try {
            if ($image && $image->isValid()) {
                $imagePath = $image->store('reports', 'public');
            }

            return DB::transaction(fn (): Report => Report::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'location_address' => $data['location_address'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'image_path' => $imagePath,
                'status' => Report::STATUS_PENDING,
                'admin_notes' => null,
            ]));
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }
    }

    /**
     * Update report status and administrator notes.
     */
    public function updateReportStatus(Report $report, string $status, ?string $adminNotes = null): Report
    {
        if (! $report->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'status' => ['Perubahan status laporan tidak mengikuti alur yang valid.'],
            ]);
        }

        $report->update([
            'status' => $status,
            'admin_notes' => $adminNotes,
        ]);

        return $report;
    }

    /**
     * Delete a report and its associated photo.
     */
    public function deleteReport(Report $report): void
    {
        if ($report->image_path && Storage::disk('public')->exists($report->image_path)) {
            Storage::disk('public')->delete($report->image_path);
        }

        $report->delete();
    }

    private function perPage(int $perPage): int
    {
        return max(1, min($perPage, 50));
    }
}
