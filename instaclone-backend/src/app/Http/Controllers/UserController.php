<?php
// src/app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function show(string $username): JsonResponse
    {
        $user = $this->userService->getByUsername($username);
        return response()->json($user);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:30', 'unique:users,username,' . $request->user()->id],
            'bio'      => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $user = $this->userService->update($request->user(), $data);
        return response()->json($user);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);
        $user = $this->userService->uploadAvatar($request->user(), $request->file('avatar'));
        return response()->json($user);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:1']]);
        $perPage = min((int) $request->query('per_page', 15), 50);
        return response()->json($this->userService->search($request->query('q'), $perPage));
    }

    public function suggestions(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 50);
        return response()->json($this->userService->suggestions($request->user()->id, $perPage));
    }

    public function posts(Request $request, int $userId): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 50);
        return response()->json($this->userService->postsByUser($userId, $perPage));
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $this->userService->deleteAccount($request->user());
        return response()->json(['message' => 'Conta deletada com sucesso.']);
    }
}