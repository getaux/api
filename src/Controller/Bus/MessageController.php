<?php

declare(strict_types=1);

namespace App\Controller\Bus;

use App\Entity\Message;
use App\Helper\RequestBodyHelper;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/messages')]
class MessageController extends AbstractController
{
    public function __construct(RequestStack $requestStack, string $apiKey)
    {
        // handle error if request stack is empty - avoid wrong code/file usage
        if (!$requestStack->getCurrentRequest()) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong...');
        }

        if ($apiKey !== $requestStack->getCurrentRequest()->headers->get('x-api-key')) {
            throw new UnauthorizedHttpException('', 'Invalid API key');
        }
    }

    #[Route(name: 'api_bus_deliver', methods: 'GET')]
    public function deliver(MessageRepository $messageRepository): Response
    {
        $message = $messageRepository->findOneBy(['status' => Message::STATUS_TODO], ['id' => 'ASC']);

        if ($message instanceof Message) {
            $message->setStatus(Message::STATUS_IN_PROGRESS);
            $message->setDeliveredAt(new \DateTimeImmutable());

            $messageRepository->add($message);

            $payload = [
                'message' => $message
            ];
        } else {
            $payload = [
                'message' => null
            ];
        }

        return $this->json($payload);
    }

    #[Route(name: 'api_bus_receive', methods: 'POST')]
    public function receive(
        Request           $request,
        MessageRepository $messageRepository
    ): Response
    {
        $body = RequestBodyHelper::map($request);

        $message = $messageRepository->findOneBy([
            'id' => $body['message_id']
        ]);

        if (!$message instanceof Message) {
            throw new NotFoundHttpException(
                sprintf('Message with id %s not found', $body['message_id'])
            );
        }

        if ($message->getStatus() !== Message::STATUS_IN_PROGRESS) {
            throw new ConflictHttpException(
                sprintf('Message with id %s already processed', $body['message_id'])
            );
        }

        $status = $body['status'] === 'OK' ? Message::STATUS_DONE : Message::STATUS_ERROR;

        $message->setStatus($status);
        $message->setResponse($body['response']);
        $message->setProcessedAt(new \DateTimeImmutable());

        $messageRepository->add($message);

        return $this->json([
            'message' => $message,
        ]);
    }

    #[Route(name: 'api_bus_receive', methods: 'OPTIONS')]
    public function options(): Response
    {
        return new Response(null, Response::HTTP_OK, [
            'Access-Control-Allow-Methods' => 'GET,POST'
        ]);
    }
}