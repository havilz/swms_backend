<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Services\Report\ReportImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateReportImagesToR2 extends Command
{
    protected $signature = 'reports:migrate-images-to-r2 {--dry-run : List legacy images without moving them}';

    protected $description = 'Move report images from the local public disk to Cloudflare R2.';

    public function handle(ReportImageStorage $imageStorage): int
    {
        $migrated = 0;
        $failed = 0;

        Report::query()->whereNotNull('image_url')->chunkById(100, function ($reports) use ($imageStorage, &$migrated, &$failed): void {
            foreach ($reports as $report) {
                $legacyPath = $report->image_url;
                if (filter_var($legacyPath, FILTER_VALIDATE_URL)) {
                    continue;
                }

                if (! Storage::disk('public')->exists($legacyPath)) {
                    $this->error("Missing local image for report {$report->id}: {$legacyPath}");
                    $failed++;

                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("Would migrate report {$report->id}: {$legacyPath}");
                    $migrated++;

                    continue;
                }

                $stream = Storage::disk('public')->readStream($legacyPath);
                if (! is_resource($stream)) {
                    $this->error("Could not read local image for report {$report->id}: {$legacyPath}");
                    $failed++;

                    continue;
                }

                $newPath = 'reports/legacy/'.$report->id.'-'.basename($legacyPath);
                try {
                    $stored = Storage::disk('r2')->writeStream($newPath, $stream);
                    if (! $stored) {
                        throw new \RuntimeException('R2 did not confirm the image upload.');
                    }

                    $report->update(['image_url' => $imageStorage->urlForPath($newPath)]);
                    if (! Storage::disk('public')->delete($legacyPath)) {
                        Log::warning('Migrated image but failed to remove its local copy.', [
                            'report_id' => $report->id,
                            'image_path' => $legacyPath,
                        ]);
                    }
                    $migrated++;
                } catch (Throwable $exception) {
                    try {
                        Storage::disk('r2')->delete($newPath);
                    } catch (Throwable $cleanupException) {
                        Log::error('Failed to clean up an incomplete R2 image migration.', [
                            'report_id' => $report->id,
                            'exception' => $cleanupException->getMessage(),
                        ]);
                    }
                    Log::error('Failed to migrate a legacy report image to R2.', [
                        'report_id' => $report->id,
                        'exception' => $exception->getMessage(),
                    ]);
                    $this->error("Failed to migrate image for report {$report->id}.");
                    $failed++;
                } finally {
                    fclose($stream);
                }
            }
        });

        $this->info(($this->option('dry-run') ? 'Would migrate ' : 'Migrated ').$migrated.' report image(s).');
        if ($failed > 0) {
            $this->error("Failed to migrate {$failed} report image(s).");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
