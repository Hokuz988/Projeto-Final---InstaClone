<?php
namespace App\Services;

use App\Models\Like;
use App\Models\Post;

class LikeService
{
    public function like(Post $post, int $userId): void
    {
        Like::firstOrCreate([
            'user_id' => $userId,
            'post_id' => $post->id,
        ]);
    }

    public function unlike(Post $post, int $userId): void
    {
        Like::where('user_id', $userId)->where('post_id', $post->id)->delete();
    }

    public function likers(Post $post, int $perPage = 20)
    {
        return $post->likes()->with('user')->paginate($perPage);
    }
}