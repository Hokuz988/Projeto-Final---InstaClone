<?php

return [

    /*
     * Define quais caminhos aceitam requisições cross-origin.
     * 'api/*' cobre todos os endpoints da API.
     * 'sanctum/csrf-cookie' é necessário se usar Sanctum com SPA cookies.
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],

    /*
     * Em desenvolvimento, aceita o origin do Vite (porta 5173).
     * Em produção, troque pelo domínio real do frontend.
     */
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * Mantenha false para autenticação via Bearer token (Sanctum token-based).
     * Use true apenas se usar cookies de sessão (SPA mode).
     */
    'supports_credentials' => false,

];
