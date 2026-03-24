<?php

namespace App\Docs;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface IngredientDocumentation
{
    #[OA\Get(
        path: '/api/ingredients',
        summary: 'List ingredients (admin only)',
        tags: ['Ingredients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Ingredients retrieved')]
    public function index();

    #[OA\Post(
        path: '/api/ingredients',
        summary: 'Create an ingredient (admin only)',
        tags: ['Ingredients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'tags'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Cheese'),
                new OA\Property(
                    property: 'tags',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['contains_lactose']
                ),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Ingredient created')]
    public function store(Request $request);

    #[OA\Put(
        path: '/api/ingredients/{id}',
        summary: 'Update an ingredient (admin only)',
        tags: ['Ingredients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Ingredient updated')]
    public function update(Request $request, Ingredient $ingredient);

    #[OA\Delete(
        path: '/api/ingredients/{id}',
        summary: 'Delete an ingredient (admin only)',
        tags: ['Ingredients'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Ingredient deleted')]
    public function destroy(Ingredient $ingredient);
}
