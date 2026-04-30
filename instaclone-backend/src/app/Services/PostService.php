<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function create(int $userId, ?string $caption, ?UploadedFile $image): Post
    {
        $path = $image->store('posts', 'public');
        return Post::create([
            'user_id' => $userId,
            'caption' => $caption,
            'image'   => $path,
        ]);
    }
    public function update(Post $post, int $authId, array $data): Post
    {
        abort_if($post->user_id !== $authId, 403, 'Acesso negado.');
        $post->update(['caption' => $data['caption'] ?? null]);
        return $post->fresh();
    }
    public function delete(Post $post, int $authId): void
    {
        abort_if($post->user_id !== $authId, 403, 'Acesso negado.');
        Storage::disk('public')->delete($post->image);
        $post->delete();
    }
}