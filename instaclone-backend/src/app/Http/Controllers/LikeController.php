<?php
// src/app/Http/Controllers/LikeController.php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\LikeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __construct(private readonly LikeService $likeService) {}

    public function like(Request $request, int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $this->likeService->like($post, $request->user()->id);
        return response()->json(['message' => 'Curtido.']);
    }

    public function unlike(Request $request, int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $this->likeService->unlike($post, $request->user()->id);
        return response()->json(['message' => 'Descurtido.']);
    }

    public function likers(Request $request, int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        $perPage = min((int) $request->query('per_page', 20), 50);
        return response()->json($this->likeService->likers($post, $perPage));
    }
}