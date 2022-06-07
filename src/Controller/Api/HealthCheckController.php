<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\HealthCheck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Health Check')]
class HealthCheckController extends AbstractController
{
    #[Route('/ping', name: 'api_health_check_ping', methods: ['GET'])]
    #[OA\Get(
        operationId: HealthCheck::GROUP_GET_HEALTH_CHECK,
        description: 'Get health of the API',
        summary: 'Get health of the API',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/HealthCheck.item'),
    )]
    public function ping(): Response
    {
        return $this->json(new HealthCheck());
    }
}
