<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'location_address' => fake()->address(),
            'latitude' => fake()->latitude(-6.3, -6.1),
            'longitude' => fake()->longitude(106.7, 106.9),
            'image_url' => null,
            'status' => fake()->randomElement(Report::STATUSES),
            'admin_notes' => null,
        ];
    }

    /**
     * Indicate that the report status is pending.
     */
    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Report::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the report status is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => Report::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Indicate that the report status is completed.
     */
    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => Report::STATUS_COMPLETED,
            'admin_notes' => 'Sampah telah berhasil diangkut oleh petugas kebersihan.',
        ]);
    }

    /**
     * Indicate that the report status is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => Report::STATUS_REJECTED,
            'admin_notes' => 'Lokasi tidak dapat diakses atau bukan area pengelolaan.',
        ]);
    }
}
