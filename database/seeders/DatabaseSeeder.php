<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = config('app.swms_admin_password');
        $demoPassword = config('app.swms_demo_password');

        if (! is_string($adminPassword) || strlen($adminPassword) < 12 || ! is_string($demoPassword) || strlen($demoPassword) < 12) {
            throw new \RuntimeException('Set SWMS_ADMIN_PASSWORD and SWMS_DEMO_PASSWORD to values of at least 12 characters before seeding.');
        }

        // 1. Admin Default Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@swms.test'],
            [
                'name' => 'Admin SWMS',
                'phone' => '081234567890',
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
            ]
        );

        // 2. Demo Normal Users
        $user1 = User::updateOrCreate(
            ['email' => 'budi@swms.test'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081298765432',
                'role' => 'user',
                'password' => Hash::make($demoPassword),
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'siti@swms.test'],
            [
                'name' => 'Siti Rahma',
                'phone' => '081345678901',
                'role' => 'user',
                'password' => Hash::make($demoPassword),
            ]
        );

        // 3. Seed Sample Reports for Users
        if (Report::count() === 0) {
            Report::create([
                'user_id' => $user1->id,
                'title' => 'Tumpukan Sampah Liar di Pinggir Jalan',
                'description' => 'Ada tumpukan sampah plastik dan limbah rumah tangga di pinggir jalan dekat jembatan, menimbulkan bau tidak sedap.',
                'location_address' => 'Jl. Kebon Jeruk No. 14, Jakarta Barat',
                'latitude' => -6.1951,
                'longitude' => 106.7725,
                'status' => Report::STATUS_PENDING,
            ]);

            Report::create([
                'user_id' => $user1->id,
                'title' => 'Tempat Sampah Umum Penuh Meluap',
                'description' => 'Kontainer sampah umum di taman bermain sudah meluap sejak kemarin lusa.',
                'location_address' => 'Taman Suropati, Menteng, Jakarta Pusat',
                'latitude' => -6.1994,
                'longitude' => 106.8326,
                'status' => Report::STATUS_IN_PROGRESS,
                'admin_notes' => 'Petugas pickup wilayah pusat sedang meluncur ke lokasi.',
            ]);

            Report::create([
                'user_id' => $user2->id,
                'title' => 'Sampah Sisa Proyek Bangunan Dibuang Sembarangan',
                'description' => 'Bekas puing dan karung semen dibiarkan di trotoar pejalan kaki.',
                'location_address' => 'Jl. Margonda Raya Km 4, Depok',
                'latitude' => -6.3725,
                'longitude' => 106.8335,
                'status' => Report::STATUS_COMPLETED,
                'admin_notes' => 'Puing berhasil dibersihkan dan diangkut dengan truk sampah.',
            ]);

            Report::create([
                'user_id' => $user2->id,
                'title' => 'Laporan Daun Kering di Halaman Rumah Pribadi',
                'description' => 'Minta tolong petugas bersihkan halaman rumah pribadi saya.',
                'location_address' => 'Komplek Griya Asri Blok C2 No 5',
                'latitude' => -6.3120,
                'longitude' => 106.8010,
                'status' => Report::STATUS_REJECTED,
                'admin_notes' => 'Layanan SWMS hanya melayani area publik dan TPS terdaftar.',
            ]);
        }
    }
}
