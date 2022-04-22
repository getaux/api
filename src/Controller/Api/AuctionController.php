<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\AuctionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/auctions')]
#[OA\Tag(name: 'Auctions')]
class AuctionController extends AbstractController
{
    #[Route(name: 'api_auctions_list', methods: 'GET')]
    public function list(AuctionRepository $auctionRepository): Response
    {
        /** @todo refactor with parameters */
        $auctions = $auctionRepository->findAll();

        return $this->json([
            'result' => $auctions
        ], Response::HTTP_OK, [], [
            'groups' => 'auction'
        ]);
    }

    #[Route('/{id}', name: 'api_auctions_show', methods: 'GET')]
    public function show(AuctionRepository $auctionRepository, string $id): Response
    {
        $auction = $auctionRepository->find((int)$id);

        if (!$auction) {
            throw new NotFoundHttpException(sprintf('Auction with id %s not found', $id));
        }

        return $this->json($auction, Response::HTTP_OK, [], [
            'groups' => 'auction'
        ]);
    }

    #[Route(name: 'api_auctions_create', methods: 'POST')]
    public function create(Request $request): Response
    {
        return $this->json([]);
    }
}
