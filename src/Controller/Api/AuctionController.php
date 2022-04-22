<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Auction;
use App\Form\AuctionType;
use App\Repository\AuctionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
    #[OA\RequestBody(
        description: 'Order to create',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'transferId',
                    description: "transferId comes from IMX SDK after transfer to escrow wallet",
                    type: 'string',
                    example: '1234567',
                ),
                new OA\Property(
                    property: 'type',
                    description: "2 types allowed here, english or dutch",
                    type: 'string',
                    example: 'dutch',
                ),
                new OA\Property(
                    property: 'quantity',
                    description: "Starting price of the auction should be linked with decimals field",
                    type: 'string',
                    example: '100000000000000',
                ),
                new OA\Property(
                    property: 'decimals',
                    description: "Decimals of quantity",
                    type: 'integer',
                    example: '18',
                ),
                new OA\Property(
                    property: 'tokenType',
                    description: "Token of auction (ETH, IMX, USDC, etc.)",
                    type: 'string',
                    example: 'ETH',
                ),
                new OA\Property(
                    property: 'endAt',
                    description: "End date of the auction",
                    type: 'datetime',
                    example: '2025-01-31T00:00:00+00:00',
                )
            ],
            type: 'object'
        )

    )]
    public function create(Request $request): Response
    {
        $auction = new Auction();
        $form = $this->createForm(AuctionType::class, $auction);

        $form->submit((array)json_decode((string)$request->getContent(), true));

        if ($form->isValid()) {
            /** @todo make API call to get details of transfer */
        } else {
            /** @todo improve me later (explode + array of errors?) */
            throw new BadRequestException((string)$form->getErrors(true));
        }

        return $this->json($auction, Response::HTTP_OK, [], [
            'groups' => 'auction'
        ]);
    }
}
