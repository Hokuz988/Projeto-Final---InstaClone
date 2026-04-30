<?php

namespace App\Services;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class FeedService
{
    public function getFeed(int $userId, int $perpage = 10){
        $followingIds = DB::table('follows')
            ->where('follower_id', $userId)
            ->pluck('following_id');

        return Post::with('user')
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as liked_by_me' => fn($q) => $q->where('user_id', $userId)])
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->paginate($perpage);
    }
}