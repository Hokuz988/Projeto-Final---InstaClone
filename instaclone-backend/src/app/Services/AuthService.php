<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Registra novo usuário e retorna token.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']), // hash bcrypt
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'access_token' => $token];
    }

    /**
     * Autentica usuário e retorna token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        // Verifica se o usuário existe e se a senha bate
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }

        // Deleta tokens antigos (opcional — força sessão única)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'access_token' => $token];
    }

    /**
     * Revoga o token atual.
     */
    public function logout(User $user): void
    {
        // currentAccessToken() retorna o token usado nesta request
        $user->currentAccessToken()->delete();
    }
}