<?php
// src/app/Services/FollowService.php
namespace App\Services;

use App\Models\Follow;
use App\Models\User;

class FollowService
{
    public function follow(int $followerId, int $followingId): void
    {
        abort_if($followerId === $followingId, 422, 'You can not follow yourself.');

        Follow::firstOrCreate([
            'follower_id'  => $followerId,
            'following_id' => $followingId,
        ]);
    }

    public function unfollow(int $followerId, int $followingId): void
    {
        Follow::where('follower_id', $followerId)
              ->where('following_id', $followingId)
              ->delete();
    }

    public function isFollowing(int $followerId, int $followingId): bool
    {
        return Follow::where('follower_id', $followerId)
                     ->where('following_id', $followingId)
                     ->exists();
    }

    public function followers(int $userId, int $perPage = 20)
    {
        return User::findOrFail($userId)->followers()->paginate($perPage);
    }

    public function following(int $userId, int $perPage = 20)
    {
        return User::findOrFail($userId)->following()->paginate($perPage);
    }
}