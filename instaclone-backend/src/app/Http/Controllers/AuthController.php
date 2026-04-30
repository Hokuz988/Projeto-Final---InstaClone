<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Injeção de dependência via construtor — o container do Laravel
    // resolve AuthService automaticamente (não precisa de new AuthService())
    public function __construct(private readonly AuthService $authService)
    {}

    public function register(RegisterRequest $request): JsonResponse
    {
        // $request->validated() retorna APENAS os campos que passaram na validação
        // nunca use $request->all() direto — risco de mass assignment
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201); // 201 Created
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json($result, 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function me(Request $request): JsonResponse
    {
        // $request->user() retorna o usuário autenticado via token Sanctum
        return response()->json($request->user());
    }
}