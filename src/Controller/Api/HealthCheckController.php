<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\HealthCheck;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Health Check')]
class HealthCheckController extends AbstractController
{
    #[Route('/ping', name: 'api_health_check_ping', methods: ['GET'])]
    #[OA\Get(
        operationId: 'get-health-check',
        description: 'Get health of the API',
        summary: 'Get health of the API'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new Model(type: HealthCheck::class)
    )]
    public function ping(): Response
    {
        return $this->json(new HealthCheck());
    }
}
