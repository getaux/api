<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    public function show(Request $request): Response
    {
        /** @var \Exception $exception */
        $exception = $request->attributes->get('exception');

        if (method_exists($exception, 'getCode') && $exception->getCode() >= 100) {
            $statusCode = $exception->getCode();
        } elseif (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json([
            'error' => $exception->getMessage(),
            'code' => $statusCode,
        ], $statusCode);
    }
}
