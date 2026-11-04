<?php

namespace App\Services\Report;

use Aws\S3\Exception\S3Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;
use RuntimeException;
use Throwable;

class ReportImageStorage
{
    public function store(UploadedFile $image): string
    {
        $baseUrl = $this->publicBaseUrl();
        $path = Storage::disk('r2')->putFile('reports', $image);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Gagal mengunggah gambar laporan ke R2.');
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }

    public function urlForPath(string $path): string
    {
        return $this->publicBaseUrl().'/'.ltrim($path, '/');
    }

    /**
     * @return array{stream: resource, mime_type: string|null, size: int|null}|null
     */
    public function open(?string $imageUrl): ?array
    {
        if ($imageUrl === null || $imageUrl === '') {
            return null;
        }

        $baseUrl = $this->publicBaseUrl();
        $disk = 'r2';
        if (str_starts_with($imageUrl, $baseUrl.'/')) {
            $path = substr($imageUrl, strlen($baseUrl) + 1);
        } elseif (! str_contains($imageUrl, '://') && str_starts_with($imageUrl, 'reports/')) {
            $disk = 'public';
            $path = $imageUrl;
        } else {
            throw new RuntimeException('URL foto laporan tidak dikenali.');
        }

        $path = rawurldecode($path);
        if (
            ! str_starts_with($path, 'reports/')
            || str_contains($path, '\\')
            || in_array('..', explode('/', $path), true)
        ) {
            throw new RuntimeException('Path foto laporan tidak valid.');
        }

        $storage = Storage::disk($disk);
        if ($disk === 'public' && ! $storage->exists($path)) {
            return null;
        }

        try {
            $stream = $storage->readStream($path);
        } catch (UnableToReadFile $exception) {
            if ($exception->getPrevious() instanceof S3Exception
                && $exception->getPrevious()->getStatusCode() === 404) {
                return null;
            }

            throw $exception;
        }
        if (! is_resource($stream)) {
            throw new RuntimeException('Foto laporan gagal dibaca dari storage.');
        }

        return [
            'stream' => $stream,
            'mime_type' => $this->mimeTypeForPath($path),
            'size' => $disk === 'public' ? $storage->size($path) : null,
        ];
    }

    private function mimeTypeForPath(string $path): ?string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => null,
        };
    }

    private function publicBaseUrl(): string
    {
        $baseUrl = rtrim((string) config('filesystems.disks.r2.url'), '/');
        $parts = parse_url($baseUrl);

        if (
            $baseUrl === ''
            || ! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || empty($parts['host'])
        ) {
            throw new RuntimeException('R2_PUBLIC_URL harus berupa URL HTTPS yang valid.');
        }

        return $baseUrl;
    }

    public function delete(?string $imageUrl): void
    {
        if ($imageUrl === null || $imageUrl === '') {
            return;
        }

        $baseUrl = rtrim((string) config('filesystems.disks.r2.url'), '/');
        if ($baseUrl !== '' && str_starts_with($imageUrl, $baseUrl.'/')) {
            $path = substr($imageUrl, strlen($baseUrl) + 1);
            $this->deleteFromDisk('r2', $path, 'Failed to delete report image from R2.', $imageUrl);

            return;
        }

        if (! str_contains($imageUrl, '://') && str_starts_with($imageUrl, 'reports/')) {
            $this->deleteFromDisk('public', $imageUrl, 'Failed to delete legacy report image.', $imageUrl);

            return;
        }

        Log::warning('Skipped deleting report image with an unrecognized URL.', ['image_url' => $imageUrl]);
    }

    private function deleteFromDisk(string $disk, string $path, string $message, string $imageUrl): void
    {
        try {
            if (! Storage::disk($disk)->delete($path)) {
                Log::warning($message, ['image_url' => $imageUrl]);
            }
        } catch (Throwable $exception) {
            Log::error($message, [
                'image_url' => $imageUrl,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
