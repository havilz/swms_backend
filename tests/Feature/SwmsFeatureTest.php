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

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_api_registration_always_creates_a_regular_user(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New Resident',
            'email' => 'resident@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()->assertJsonPath('data.user.role', 'user');
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

    public function test_user_can_create_report_with_validated_image_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/reports', [
            'title' => 'Sampah di taman',
            'description' => 'Tumpukan sampah menghalangi jalan.',
            'location_address' => 'Taman Kota',
            'latitude' => '-6.2',
            'longitude' => '106.8',
            'image' => UploadedFile::fake()->createWithContent(
                'trash.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        $report = Report::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame(Report::STATUS_PENDING, $report->status);
        Storage::disk('public')->assertExists($report->image_path);
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

    public function test_admin_can_update_report_status_but_must_follow_workflow(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->pending()->create();

        $this->actingAs($admin)
            ->patch(route('admin.reports.status', $report), ['status' => Report::STATUS_COMPLETED])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->patch(route('admin.reports.status', $report), ['status' => Report::STATUS_IN_PROGRESS])
            ->assertRedirect(route('admin.reports.show', $report));

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => Report::STATUS_IN_PROGRESS]);

        $this->actingAs($admin)
            ->patch(route('admin.reports.status', $report), ['status' => Report::STATUS_COMPLETED])
            ->assertRedirect(route('admin.reports.show', $report));
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_regular_user_cannot_update_report_status(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->pending()->create();

        $this->actingAs($user)
            ->patch(route('admin.reports.status', $report), ['status' => Report::STATUS_IN_PROGRESS])
            ->assertForbidden();
    }

    public function test_admin_can_create_user_from_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Created User',
                'email' => 'created@example.com',
                'role' => 'user',
                'password' => 'password123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'created@example.com', 'role' => 'user']);
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
