<?php

namespace App\Docs;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface UserDocumentation
{
    #[OA\Get(
        path: '/api/me',
        summary: 'Get the authenticated user',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Authenticated user retrieved')]
    public function me(Request $request);
}
