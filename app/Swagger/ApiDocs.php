<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(version: "1.0.0", description: "Documentation complète de notre API", title: "API Brigade")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer"
)]
class ApiDocs
{

}