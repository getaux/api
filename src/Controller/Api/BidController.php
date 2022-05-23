<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Auction;
use App\Entity\Bid;
use App\Form\AuctionType;
use App\Helper\ResponseHelper;
use App\Repository\AuctionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/bids')]
#[OA\Tag(name: 'Bids')]
class BidController extends AbstractController
{
    #[Route(name: 'api_bids_list', methods: 'GET')]
    #[OA\Get(
        operationId: Bid::GROUP_GET_BIDS,
        description: 'Get a list of bids',
        summary: 'Get a list of bids'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/GetAuctionWithAsset')
                )
            ],
        )
    )]
    public function list(AuctionRepository $auctionRepository): Response
    {
        /** @todo refactor with parameters */
        $auctions = $auctionRepository->findAll();

        return $this->json([
            'result' => $auctions
        ], Response::HTTP_OK, [], [
            'groups' => [Auction::GROUP_GET_AUCTION, Auction::GROUP_GET_AUCTION_WITH_ASSET]
        ]);
    }

    #[Route('/{id}', name: 'api_bids_show', methods: 'GET')]
    #[OA\Get(
        operationId: Bid::GROUP_GET_BID,
        description: 'Get details of a bid',
        summary: 'Get details of a bid'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/GetAuctionWithAsset')
    )]
    public function show(AuctionRepository $auctionRepository, string $id): Response
    {
        $auction = $auctionRepository->find((int)$id);

        if (!$auction) {
            throw new NotFoundHttpException(sprintf('Auction with id %s not found', $id));
        }

        return $this->json($auction, Response::HTTP_OK, [], [
            'groups' => [Auction::GROUP_GET_AUCTION, Auction::GROUP_GET_AUCTION_WITH_ASSET]
        ]);
    }

    #[Route(name: 'api_bids_create', methods: 'POST')]
    #[OA\Post(
        operationId: Bid::GROUP_POST_BID,
        description: 'Create a bid',
        summary: 'Create a bid'
    )]
    #[OA\RequestBody(
        description: 'Auction to create',
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/PostAuction',
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Created',
        content: new OA\JsonContent(ref: '#/components/schemas/GetAuctionWithAsset')
    )]
    public function create(Request $request, AuctionRepository $auctionRepository): Response
    {
        $auction = new Bid();
        $form = $this->createForm(AuctionType::class, $auction);

        $form->submit((array)json_decode((string)$request->getContent(), false));

        if ($form->isValid()) {
            //$auctionRepository->add($auction);
        } else {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        return $this->json($auction, Response::HTTP_OK, [], [
            'groups' => [Auction::GROUP_GET_AUCTION, Auction::GROUP_GET_AUCTION_WITH_ASSET]
        ]);
    }
}
