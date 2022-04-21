<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Health Check')]
class HealthCheckController extends AbstractController
{
    #[Route('/ping', name: 'health_check_ping', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'I\'m alive!',
    )]
    public function ping(): Response
    {
        return $this->json([
            'result' => 'pong',
        ]);
    }
}
