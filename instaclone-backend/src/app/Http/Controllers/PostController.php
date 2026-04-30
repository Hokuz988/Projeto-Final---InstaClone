<?php
// src/app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private readonly PostService $postService) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'image'   => ['required', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:2200'],
        ]);

        $post = $this->postService->create(
            $request->user()->id,
            $data['caption'] ?? null,
            $request->file('image')
        );

        return response()->json($post->load('user'), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $post = Post::with(['user', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as liked_by_me' => fn($q) => $q->where('user_id', $request->user()->id)])
            ->findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['caption' => ['nullable', 'string', 'max:2200']]);
        $post = Post::findOrFail($id);
        $post = $this->postService->update($post, $request->user()->id, $data);
        return response()->json($post);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $this->postService->delete($post, $request->user()->id);
        return response()->json(['message' => 'Post deletado.']);
    }
}