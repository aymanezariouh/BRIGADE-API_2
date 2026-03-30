<?php

namespace App\Docs;

use App\Models\Plate;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

interface RecommendationDocumentation
{
    #[OA\Post(
        path: '/api/recommendations/analyze/{id}',
        summary: 'Launch recommendation analysis for a plate',
        tags: ['Recommendations'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 202, description: 'Recommendation analysis queued')]
    public function analyze(Request $request, Plate $plate);

    #[OA\Get(
        path: '/api/recommendations',
        summary: 'List my recommendation history',
        tags: ['Recommendations'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Recommendations retrieved')]
    public function index(Request $request);

    #[OA\Get(
        path: '/api/recommendations/{id}',
        summary: 'Get the latest recommendation for a plate',
        tags: ['Recommendations'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Recommendation retrieved')]
    public function show(Request $request, Plate $plate);
}
