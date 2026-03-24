<?php

namespace App\Docs;

use App\Models\Category;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface CategoryDocumentation
{
    #[OA\Get(
        path: '/api/categories',
        summary: 'List categories',
        tags: ['Categories'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Categories retrieved')]
    public function index(Request $request);

    #[OA\Post(
        path: '/api/categories',
        summary: 'Create a category (admin only)',
        tags: ['Categories'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Desserts'),
                new OA\Property(property: 'description', type: 'string', example: 'Sweet dishes'),
                new OA\Property(property: 'color', type: 'string', example: '#FF5733'),
                new OA\Property(property: 'is_active', type: 'boolean', example: true),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Category created')]
    public function store(Request $request);

    #[OA\Get(
        path: '/api/categories/{id}',
        summary: 'Show a category',
        tags: ['Categories'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Category retrieved')]
    public function show(Request $request, Category $category);

    #[OA\Put(
        path: '/api/categories/{id}',
        summary: 'Update a category (admin only)',
        tags: ['Categories'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Healthy Desserts'),
                new OA\Property(property: 'description', type: 'string', example: 'Light sweet dishes'),
                new OA\Property(property: 'color', type: 'string', example: '#00AA55'),
                new OA\Property(property: 'is_active', type: 'boolean', example: false),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Category updated')]
    public function update(Request $request, Category $category);

    #[OA\Delete(
        path: '/api/categories/{id}',
        summary: 'Delete a category (admin only)',
        tags: ['Categories'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Category deleted')]
    public function destroy(Category $category);

    #[OA\Get(
        path: '/api/categories/{id}/plates',
        summary: 'List plates for a category',
        tags: ['Categories'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Category plates retrieved')]
    public function plates(Request $request, Category $category);
}
