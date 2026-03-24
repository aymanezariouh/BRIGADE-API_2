<?php

namespace App\Docs;

use App\Models\Plate;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface PlatDocumentation
{
    #[OA\Get(
        path: '/api/plates',
        summary: 'List plates',
        tags: ['Plates'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Plates retrieved')]
    public function index(Request $request);

    #[OA\Post(
        path: '/api/plates',
        summary: 'Create a plate (admin only)',
        tags: ['Plates'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'price', 'category_id'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Pasta Alfredo'),
                new OA\Property(property: 'description', type: 'string', example: 'Creamy pasta'),
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 12.5),
                new OA\Property(property: 'is_available', type: 'boolean', example: true),
                new OA\Property(property: 'category_id', type: 'integer', example: 1),
                new OA\Property(
                    property: 'ingredient_ids',
                    type: 'array',
                    items: new OA\Items(type: 'integer'),
                    example: [1, 2]
                ),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Plate created')]
    public function store(Request $request);

    #[OA\Get(
        path: '/api/plates/{id}',
        summary: 'Show a plate',
        tags: ['Plates'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Plate retrieved')]
    public function show(Request $request, Plate $plate);

    #[OA\Put(
        path: '/api/plates/{id}',
        summary: 'Update a plate (admin only)',
        tags: ['Plates'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'price', 'category_id'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Pasta Verde'),
                new OA\Property(property: 'description', type: 'string', example: 'Green pasta'),
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 13.75),
                new OA\Property(property: 'is_available', type: 'boolean', example: false),
                new OA\Property(property: 'category_id', type: 'integer', example: 1),
                new OA\Property(
                    property: 'ingredient_ids',
                    type: 'array',
                    items: new OA\Items(type: 'integer'),
                    example: [1]
                ),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Plate updated')]
    public function update(Request $request, Plate $plate);

    #[OA\Delete(
        path: '/api/plates/{id}',
        summary: 'Delete a plate (admin only)',
        tags: ['Plates'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Plate deleted')]
    public function destroy(Plate $plate);
}
