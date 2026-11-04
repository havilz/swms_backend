<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminReportClassificationTest extends TestCase
{
    use RefreshDatabase;

    private const IMAGE_CONTENT = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    public function test_admin_can_get_a_non_persisted_classification_suggestion(): void
    {
        config([
            'filesystems.disks.r2.url' => 'https://images.example.test',
            'services.waste_model.url' => 'https://model.example.test/predict',
            'services.waste_model.token' => 'model-service-secret',
        ]);
        Storage::fake('r2');
        Http::preventStrayRequests();
        Http::fake([
            'model.example.test/predict' => Http::response([
                'predicted_class' => 'Plastic Waste',
                'confidence' => 0.9,
                'probabilities' => [
                    'Organic Waste' => 0.05,
                    'Plastic Waste' => 0.9,
                    'Paper Waste' => 0.05,
                ],
                'model_version' => 'test-model',
            ]),
        ]);
        $imageBytes = base64_decode(self::IMAGE_CONTENT, true);
        Storage::disk('r2')->put('reports/sample.png', $imageBytes);
        $report = Report::factory()->pending()->create([
            'image_url' => 'https://images.example.test/reports/sample.png',
        ]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/reports/{$report->id}/classify")
            ->assertOk()
            ->assertJsonPath('predicted_class', 'Plastic Waste')
            ->assertJsonPath('confidence', 0.9)
            ->assertJsonPath('model_version', 'test-model')
            ->assertJsonMissingPath('data');

        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://model.example.test/predict'
            && $request->isMultipart()
            && $request->hasFile('file')
            && $request->hasHeader('Authorization', 'Bearer model-service-secret'));
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => Report::STATUS_PENDING,
            'admin_notes' => null,
        ]);
    }

    public function test_only_authenticated_admins_can_request_classification(): void
    {
        $report = Report::factory()->create();

        $this->postJson("/api/v1/admin/reports/{$report->id}/classify")
            ->assertUnauthorized();

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/admin/reports/{$report->id}/classify")
            ->assertForbidden();
    }

    public function test_classification_returns_not_found_without_a_report_image(): void
    {
        config([
            'filesystems.disks.r2.url' => 'https://images.example.test',
            'services.waste_model.url' => 'https://model.example.test/predict',
            'services.waste_model.token' => 'model-service-secret',
        ]);
        Storage::fake('r2');
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->create(['image_url' => null]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/reports/{$report->id}/classify")
            ->assertNotFound()
            ->assertJsonPath('message', 'Foto laporan tidak ditemukan.');
    }

    public function test_classification_rejects_an_invalid_upstream_response(): void
    {
        config([
            'filesystems.disks.r2.url' => 'https://images.example.test',
            'services.waste_model.url' => 'https://model.example.test/predict',
            'services.waste_model.token' => 'model-service-secret',
        ]);
        Storage::fake('r2');
        Http::preventStrayRequests();
        Http::fake([
            'model.example.test/predict' => Http::response([
                'predicted_class' => 'Other',
                'confidence' => 1.0,
                'probabilities' => [
                    'Organic Waste' => 1.0,
                    'Plastic Waste' => 0.0,
                    'Paper Waste' => 0.0,
                ],
                'model_version' => 'test-model',
            ]),
        ]);
        Storage::disk('r2')->put('reports/sample.png', base64_decode(self::IMAGE_CONTENT, true));
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->create([
            'image_url' => 'https://images.example.test/reports/sample.png',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/reports/{$report->id}/classify")
            ->assertStatus(502)
            ->assertJsonPath('message', 'Layanan klasifikasi mengirim respons yang tidak valid.');
    }

    public function test_classification_requires_model_service_configuration(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->create();
        config([
            'services.waste_model.url' => '',
            'services.waste_model.token' => '',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/reports/{$report->id}/classify")
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Layanan klasifikasi belum dikonfigurasi.');
    }

    public function test_classification_rejects_an_oversized_storage_image(): void
    {
        config([
            'filesystems.disks.r2.url' => 'https://images.example.test',
            'services.waste_model.url' => 'https://model.example.test/predict',
            'services.waste_model.token' => 'model-service-secret',
        ]);
        Storage::fake('r2');
        Http::preventStrayRequests();
        Storage::disk('r2')->put('reports/large.png', str_repeat('x', 10 * 1024 * 1024 + 1));
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->create([
            'image_url' => 'https://images.example.test/reports/large.png',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/reports/{$report->id}/classify")
            ->assertStatus(413)
            ->assertJsonPath('message', 'Ukuran foto melebihi batas 10 MiB.');
    }
}
