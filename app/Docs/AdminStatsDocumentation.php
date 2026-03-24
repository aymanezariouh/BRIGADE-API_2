<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

interface AdminStatsDocumentation
{
    #[OA\Get(
        path: '/api/admin/stats',
        summary: 'Get global admin statistics',
        tags: ['Admin'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Statistics retrieved')]
    public function show();
}
