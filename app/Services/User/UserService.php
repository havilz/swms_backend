<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\Report\ReportImageStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected ReportImageStorage $imageStorage,
    ) {}

    /**
     * Get paginated list of users with search and role filter.
     */
    public function getUsersList(?string $role = null, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::withCount('reports');

        if ($role && in_array($role, ['admin', 'user'], true)) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(max(1, min($perPage, 50)))->withQueryString();
    }

    /**
     * Create a new user or administrator.
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'user',
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? $user->role,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user;
    }

    /**
     * Delete user with self-deletion protection.
     *
     * @throws ValidationException
     */
    public function deleteUser(User $user, int $currentAdminId): bool
    {
        if ($user->id === $currentAdminId) {
            throw ValidationException::withMessages([
                'user' => ['Anda tidak dapat menghapus akun Anda sendiri.'],
            ]);
        }

        $imageUrls = $user->reports()->whereNotNull('image_url')->pluck('image_url');
        $deleted = DB::transaction(fn (): bool => (bool) $user->delete());

        if ($deleted) {
            foreach ($imageUrls as $imageUrl) {
                $this->imageStorage->delete($imageUrl);
            }
        }

        return $deleted;
    }
}
