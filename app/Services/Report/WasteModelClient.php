<?php

namespace App\Services\Report;

use App\Models\Report;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WasteModelClient
{
    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;

    private const CLASSES = ['Organic Waste', 'Plastic Waste', 'Paper Waste'];

    public function __construct(
        private readonly ReportImageStorage $imageStorage,
    ) {}

    /**
     * @return array{
     *     predicted_class: string,
     *     confidence: float,
     *     probabilities: array<string, float>,
     *     model_version: string
     * }
     */
    public function classify(Report $report): array
    {
        $url = config('services.waste_model.url');
        $token = config('services.waste_model.token');

        $urlParts = is_string($url) ? parse_url($url) : false;
        if (
            ! is_array($urlParts)
            || ($urlParts['scheme'] ?? null) !== 'https'
            || empty($urlParts['host'])
            || isset($urlParts['user'])
            || isset($urlParts['pass'])
            || ! is_string($token)
            || $token === ''
        ) {
            throw new ModelClassificationException('Layanan klasifikasi belum dikonfigurasi.', 503);
        }

        $image = $this->imageStorage->open($report->image_url);
        if ($image === null) {
            throw new ModelClassificationException('Foto laporan tidak ditemukan.', 404);
        }

        $mimeType = $image['mime_type'];
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            fclose($image['stream']);

            throw new ModelClassificationException('Format foto laporan tidak didukung.', 415);
        }
        if ($image['size'] !== null && $image['size'] > self::MAX_IMAGE_BYTES) {
            fclose($image['stream']);

            throw new ModelClassificationException('Ukuran foto melebihi batas 10 MiB.', 413);
        }

        try {
            $contents = stream_get_contents($image['stream'], self::MAX_IMAGE_BYTES + 1);
        } finally {
            fclose($image['stream']);
        }

        if (! is_string($contents)) {
            throw new ModelClassificationException('Foto laporan gagal dibaca dari storage.', 502);
        }
        if (strlen($contents) > self::MAX_IMAGE_BYTES) {
            throw new ModelClassificationException('Ukuran foto melebihi batas 10 MiB.', 413);
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };

        try {
            $response = Http::connectTimeout(3)
                ->timeout(20)
                ->withOptions(['allow_redirects' => false])
                ->withToken($token)
                ->attach('file', $contents, "report-image.{$extension}", ['Content-Type' => $mimeType])
                ->post($url);
        } catch (ConnectionException) {
            throw new ModelClassificationException('Layanan klasifikasi tidak dapat dihubungi.', 503);
        }

        if (! $response->successful()) {
            throw new ModelClassificationException('Layanan klasifikasi gagal memproses foto.', 502);
        }

        $result = $response->json();
        if (! is_array($result)) {
            throw new ModelClassificationException('Layanan klasifikasi mengirim respons yang tidak valid.', 502);
        }

        return $this->validatedResult($result);
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array{
     *     predicted_class: string,
     *     confidence: float,
     *     probabilities: array<string, float>,
     *     model_version: string
     * }
     */
    private function validatedResult(array $result): array
    {
        $predictedClass = $result['predicted_class'] ?? null;
        $confidence = $result['confidence'] ?? null;
        $probabilities = $result['probabilities'] ?? null;
        $modelVersion = $result['model_version'] ?? null;

        if (
            ! is_string($predictedClass)
            || ! in_array($predictedClass, self::CLASSES, true)
            || (! is_int($confidence) && ! is_float($confidence))
            || ! is_array($probabilities)
            || count($probabilities) !== count(self::CLASSES)
            || ! is_string($modelVersion)
            || $modelVersion === ''
            || strlen($modelVersion) > 100
        ) {
            throw new ModelClassificationException('Layanan klasifikasi mengirim respons yang tidak valid.', 502);
        }

        $validatedProbabilities = [];
        foreach (self::CLASSES as $class) {
            $probability = $probabilities[$class] ?? null;
            if (
                (! is_int($probability) && ! is_float($probability))
                || ! is_finite((float) $probability)
                || (float) $probability < 0
                || (float) $probability > 1
            ) {
                throw new ModelClassificationException('Layanan klasifikasi mengirim respons yang tidak valid.', 502);
            }
            $validatedProbabilities[$class] = (float) $probability;
        }

        $confidence = (float) $confidence;
        if (
            $confidence < 0
            || $confidence > 1
            || ! is_finite($confidence)
            || abs(array_sum($validatedProbabilities) - 1) > 0.01
        ) {
            throw new ModelClassificationException('Layanan klasifikasi mengirim respons yang tidak valid.', 502);
        }

        return [
            'predicted_class' => $predictedClass,
            'confidence' => $confidence,
            'probabilities' => $validatedProbabilities,
            'model_version' => $modelVersion,
        ];
    }
}
