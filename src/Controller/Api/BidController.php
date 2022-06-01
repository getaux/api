<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Bid;
use App\Form\AddBidType;
use App\Form\Filters\FilterAuctionsType;
use App\Form\Filters\FilterBidsType;
use App\Helper\ResponseHelper;
use App\Helper\SortHelper;
use App\Repository\BidRepository;
use App\Service\FilterService;
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
        summary: 'Get a list of bids',
        parameters: [
            new OA\Parameter(
                name: 'page_size',
                description: 'Page size of the result',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of the result (for paginate)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'order_by',
                description: 'Property to sort by',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(enum: FilterAuctionsType::ORDER_FIELDS),
                ],
            ),
            new OA\Parameter(
                name: 'direction',
                description: 'Direction to sort (asc/desc)',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'direction', enum: SortHelper::WAYS),
                ],
            ),
            new OA\Parameter(
                name: 'status',
                description: 'Status of these auctions',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'status', enum: Bid::STATUS),
                ],
            ),
            new OA\Parameter(
                name: 'auction_id',
                description: 'AuctionX Internal Auction ID',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'auction_id', type: 'string'),
                ],
            ),
        ],
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Bid.item'),
                ),
            ],
        ),
    )]
    public function list(Request $request, FilterService $filterService, BidRepository $bidRepository): Response
    {
        $form = $this->createForm(FilterBidsType::class);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        list($filters, $order, $limit, $offset) = $filterService->map((array)$form->getData());

        $totalAuctions = $bidRepository->customCount($filters);
        $bids = $bidRepository->customFindAll($filters, $order, $limit, $offset);

        return $this->json([
            'result' => $bids,
            'totalResults' => $totalAuctions,
        ], Response::HTTP_OK, [], [
            'groups' => [
                Bid::GROUP_GET_BID,
                Bid::GROUP_GET_BID_WITH_AUCTION,
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_bids_show', methods: 'GET')]
    #[OA\Get(
        operationId: Bid::GROUP_GET_BID,
        description: 'Get details of a bid',
        summary: 'Get details of a bid',
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Bid.item'),
    )]
    public function show(BidRepository $bidRepository, string $id): Response
    {
        $bid = $bidRepository->find((int)$id);

        if (!$bid) {
            throw new NotFoundHttpException(sprintf('Bid with id %s not found', $id));
        }

        return $this->json($bid, Response::HTTP_OK, [], [
            'groups' => [
                Bid::GROUP_GET_BID,
                Bid::GROUP_GET_BID_WITH_AUCTION,
            ],
        ]);
    }

    #[Route(name: 'api_bids_create', methods: 'POST')]
    #[OA\Post(
        operationId: Bid::GROUP_POST_BID,
        description: 'Create a bid',
        summary: 'Create a bid',
    )]
    #[OA\RequestBody(
        description: 'Bid to create',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'transferId',
                    title: 'transferId',
                    description: 'IMX transfer ID (bid deposit)',
                    type: 'string',
                    example: '4452442',
                ),
                new OA\Property(
                    property: 'auctionId',
                    description: 'AuctionX internal ID of the auction',
                    type: 'integer',
                    example: 1
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: 201,
        description: 'Created',
        content: new OA\JsonContent(ref: '#/components/schemas/Bid.item'),
    )]
    public function create(Request $request, BidRepository $bidRepository): Response
    {
        $bid = new Bid();
        $form = $this->createForm(AddBidType::class, $bid);

        $form->submit((array)json_decode((string)$request->getContent(), false));

        if ($form->isValid()) {
            //$auctionRepository->add($auction);
        } else {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        return $this->json($bid, Response::HTTP_OK, [], [
            'groups' => [
                Bid::GROUP_GET_BID,
                Bid::GROUP_GET_BID_WITH_AUCTION,
            ]
        ]);
    }
}
