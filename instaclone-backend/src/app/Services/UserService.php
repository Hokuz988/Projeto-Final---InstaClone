<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function getByUsername(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }
    public function update(User $user, array $data): User
    {
        $user->update(array_filter([
            'name' => $data['name'] ?? null,
            'username' => $data['username'] ?? null,
            'bio'  => $data['bio'] ?? null,
        ], fn ($v) => $v !== null));
        return $user->fresh();
    }
    public function uploadAvatar(User $user, UploadedFile $file): User
    {
        if ($raw = $user->getRawOriginal('avatar_url')) {
            Storage::disk('public')->delete($raw);
        }
        $path = $file->store('avatars', 'public');
        $user->update(['avatar_url' => $path]);
        return $user->fresh();
    }
    public function search (string $query, int $perPage = 20)
    {
        return User::where('username', 'like', "%$query%")
                    ->orWhere('name', 'like', "%$query%")
                    ->paginate($perPage);
    }
    public function suggestions(int $userId, int $perPage = 20)
    {
        $followingIds = User::findOrFail($userId)->following()->pluck('users.id');
        return User::whereNotIn('id', $followingIds)
                    ->where('id', '!=', $userId)
                    ->inRandomOrder()
                    ->paginate($perPage);
    }
    public function postsByUser(int $userId, int $perPage = 10)
    {
        return User::findOrFail($userId)
            ->posts()->with('user')
            ->withCount(['likes', 'comments'])
            ->latest()->paginate($perPage);
    }

    public function deleteAccount(User $user): void
    {
        if ($raw = $user->getRawOriginal('avatar_url')) {
            Storage::disk('public')->delete($raw);
        }
        $user->tokens()->delete();
        $user->delete();
    }
}