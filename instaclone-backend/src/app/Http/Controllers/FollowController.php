<?php
// src/app/Http/Controllers/FollowController.php
namespace App\Http\Controllers;

use App\Services\FollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(private readonly FollowService $followService) {}

    public function follow(Request $request, int $userId): JsonResponse
    {
        $this->followService->follow($request->user()->id, $userId);
        return response()->json(['message' => 'Seguindo.']);
    }

    public function unfollow(Request $request, int $userId): JsonResponse
    {
        $this->followService->unfollow($request->user()->id, $userId);
        return response()->json(['message' => 'Deixou de seguir.']);
    }

    public function isFollowing(Request $request, int $userId): JsonResponse
    {
        $result = $this->followService->isFollowing($request->user()->id, $userId);
        return response()->json(['is_following' => $result]);
    }

    public function followers(Request $request, int $userId): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 50);
        return response()->json($this->followService->followers($userId, $perPage));
    }

    public function following(Request $request, int $userId): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 50);
        return response()->json($this->followService->following($userId, $perPage));
    }
}