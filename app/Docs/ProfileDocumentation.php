<?php

namespace App\Docs;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface ProfileDocumentation
{
    #[OA\Get(
        path: '/api/profile',
        summary: 'Get the authenticated user dietary profile',
        tags: ['Profile'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Profile retrieved')]
    public function show(Request $request);

    #[OA\Put(
        path: '/api/profile',
        summary: 'Update the authenticated user dietary profile',
        tags: ['Profile'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['dietary_tags'],
            properties: [
                new OA\Property(
                    property: 'dietary_tags',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['gluten_free', 'no_lactose']
                ),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Profile updated')]
    public function update(Request $request);
}
