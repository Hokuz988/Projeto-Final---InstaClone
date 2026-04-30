<?php
namespace App\Services;

use App\Models\Comment;
use App\Models\Post;

class CommentService
{
    public function list(Post $post, int $perPage = 10)
    {
        return $post->comments()->with('user')->paginate($perPage);
    }

    public function create(Post $post, int $userId, string $content)
    {
        $comment = Comment::create([
            'post_id' => $post->id,
            'content' => $content,
            'user_id' => $userId,
        ]);
        return $comment->load('user');
    }

    public function update(Comment $comment, int $userId, string $content)
    {
        abort_if($comment->user_id !== $userId, 403, 'Unauthorized action.');
        $comment->content = $content;
        $comment->save();
        return $comment;
    }

    public function delete(Comment $comment, int $userId): void
    {
        abort_if($comment->user_id !== $userId, 403, 'Unauthorized action.');
        $comment->delete();
    }
}
