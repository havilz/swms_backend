<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SwmsFeatureTest extends TestCase
{
    use RefreshDatabase;

    private const IMAGE_CONTENT = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    protected function fakeR2(): void
    {
        config(['filesystems.disks.r2.url' => 'https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev']);
        Storage::fake('r2');
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_dashboard_only_shows_health_checks(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('SWMS API Health')
            ->assertSee('/up')
            ->assertDontSee('Laporan terbaru');
    }

    public function test_api_registration_always_creates_a_regular_user(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New Resident',
            'email' => 'resident@example.com',
            'role' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()->assertJsonPath('data.user.role', 'user');
        $this->assertIsString($response->json('data.token'));
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('users', ['email' => 'resident@example.com', 'role' => 'user']);
    }

    public function test_admin_cannot_use_mobile_report_endpoints(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/reports')->assertForbidden();
    }

    public function test_guest_cannot_use_mobile_report_endpoints(): void
    {
        $this->getJson('/api/v1/reports')->assertUnauthorized();
    }

    public function test_user_can_only_view_owned_report(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $report = Report::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/v1/reports/{$report->id}")
            ->assertForbidden();
    }

    public function test_user_can_create_report_with_image_saved_to_r2_as_a_url(): void
    {
        $this->fakeR2();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/reports', [
            'title' => 'Sampah di taman',
            'description' => 'Tumpukan sampah menghalangi jalan.',
            'location_address' => 'Taman Kota',
            'latitude' => '-6.2',
            'longitude' => '106.8',
            'image' => UploadedFile::fake()->createWithContent(
                'trash.png',
                base64_decode(self::IMAGE_CONTENT, true),
            ),
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        $report = Report::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(Report::STATUS_PENDING, $report->status);
        $this->assertStringStartsWith('https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/reports/', $report->image_url);
        $response->assertJsonPath('data.image_url', $report->image_url);
        $response->assertJsonPath('data.image_path', $report->image_url);

        $imageKey = substr($report->image_url, strlen('https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/'));
        Storage::disk('r2')->assertExists($imageKey);
    }

    public function test_report_upload_rejects_non_image_files(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->post('/api/v1/reports', [
                'title' => 'Invalid upload',
                'description' => 'This upload must be rejected.',
                'location_address' => 'Taman Kota',
                'image' => UploadedFile::fake()->create('payload.svg', 10, 'image/svg+xml'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_user_can_edit_owned_report_without_changing_admin_managed_fields(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->pending()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->id}", [
                'title' => 'Judul diperbarui',
                'status' => Report::STATUS_COMPLETED,
                'admin_notes' => 'Tidak boleh diubah user',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Judul diperbarui')
            ->assertJsonPath('data.status', Report::STATUS_PENDING)
            ->assertJsonPath('data.admin_notes', null);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'title' => 'Judul diperbarui',
            'status' => Report::STATUS_PENDING,
            'admin_notes' => null,
        ]);
    }

    public function test_user_cannot_edit_another_users_report(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $report = Report::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->id}", ['title' => 'Unauthorized'])
            ->assertForbidden();

        $this->assertDatabaseMissing('reports', [
            'id' => $report->id,
            'title' => 'Unauthorized',
        ]);
    }

    public function test_user_can_replace_and_remove_report_image_on_r2(): void
    {
        $this->fakeR2();
        $user = User::factory()->create();
        $oldKey = 'reports/old-image.png';
        Storage::disk('r2')->put($oldKey, 'old image');
        $report = Report::factory()->create([
            'user_id' => $user->id,
            'image_url' => 'https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/'.$oldKey,
        ]);

        $this->actingAs($user, 'sanctum')
            ->post("/api/v1/reports/{$report->id}", [
                '_method' => 'PATCH',
                'title' => 'Report with replacement image',
                'image' => UploadedFile::fake()->createWithContent(
                    'replacement.png',
                    base64_decode(self::IMAGE_CONTENT, true),
                ),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $report->refresh();
        $this->assertNotSame('https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/'.$oldKey, $report->image_url);
        $newKey = substr($report->image_url, strlen('https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/'));
        Storage::disk('r2')->assertExists($newKey);
        Storage::disk('r2')->assertMissing($oldKey);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->id}", ['remove_image' => true])
            ->assertOk()
            ->assertJsonPath('data.image_url', null);

        Storage::disk('r2')->assertMissing($newKey);
    }

    public function test_user_report_update_requires_at_least_one_editable_field(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/reports/{$report->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('report');
    }

    public function test_user_cannot_access_admin_api_but_admin_can_update_report_status(): void
    {
        $report = Report::factory()->pending()->create();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/reports')
            ->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/reports/{$report->id}/status", [
                'status' => Report::STATUS_IN_PROGRESS,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Report::STATUS_IN_PROGRESS);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/reports/{$report->id}/status", [
                'status' => Report::STATUS_COMPLETED,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Report::STATUS_COMPLETED);
    }

    public function test_admin_status_update_must_follow_report_workflow(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->pending()->create();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/reports/{$report->id}/status", [
                'status' => Report::STATUS_COMPLETED,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_admin_can_create_user_through_json_api(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/users', [
                'name' => 'Created User',
                'email' => 'created@example.com',
                'role' => 'user',
                'password' => 'password123',
            ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'created@example.com');

        $this->assertDatabaseHas('users', ['email' => 'created@example.com', 'role' => 'user']);
    }

    public function test_admin_deleting_user_cleans_up_owned_report_images_from_r2(): void
    {
        $this->fakeR2();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $imageKey = 'reports/user-image.png';
        Storage::disk('r2')->put($imageKey, 'image');
        $report = Report::factory()->create([
            'user_id' => $user->id,
            'image_url' => 'https://pub-c956ace443384e5d97c55b5406d3c802.r2.dev/'.$imageKey,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/users/{$user->id}")
            ->assertOk();

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
        Storage::disk('r2')->assertMissing($imageKey);
    }

    public function test_regular_user_cannot_access_admin_health_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_login_rejects_regular_user(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    }
}
