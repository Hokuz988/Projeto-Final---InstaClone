<?php
// src/app/Http/Controllers/FeedController.php
namespace App\Http\Controllers;

use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(private readonly FeedService $feedService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 10), 50);
        $feed = $this->feedService->getFeed($request->user()->id, $perPage);
        return response()->json($feed);
    }
}