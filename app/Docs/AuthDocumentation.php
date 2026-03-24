<?php

namespace App\Docs;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface AuthDocumentation
{
    #[OA\Post(
        path: '/api/register',
        summary: 'Register a customer account with dietary tags',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Alice Customer'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'alice@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                new OA\Property(
                    property: 'dietary_tags',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['vegan', 'gluten_free']
                ),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'User registered successfully')]
    public function register(Request $request);

    #[OA\Post(
        path: '/api/login',
        summary: 'Login and get a Sanctum token',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'alice@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Login successful')]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    public function login(Request $request);

    #[OA\Post(
        path: '/api/logout',
        summary: 'Logout the current authenticated user',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Logout successful')]
    public function logout(Request $request);
}
