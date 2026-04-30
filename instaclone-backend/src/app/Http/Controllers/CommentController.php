<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService) {}

    public function index(Request $request, int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $perPage = min((int) $request->query('per_page', 10), 50);
        return response()->json($this->commentService->list($post, $perPage));
    }

    public function store(Request $request, int $postId): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:1000']]);
        $post = Post::findOrFail($postId);
        $comment = $this->commentService->create($post, $request->user()->id, $data['body']);
        return response()->json($comment, 201);
    }

    public function update(Request $request, int $commentId): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:1000']]);
        $comment = Comment::findOrFail($commentId);
        $comment = $this->commentService->update($comment, $request->user()->id, $data['body']);
        return response()->json($comment);
    }

    public function destroy(Request $request, int $commentId): JsonResponse
    {
        $comment = Comment::findOrFail($commentId);
        $this->commentService->delete($comment, $request->user()->id);
        return response()->json(['message' => 'Comentário deletado.']);
    }
}