<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Auction;
use App\Entity\Bid;
use App\Form\AddBidType;
use App\Form\CancelBidType;
use App\Form\Filters\FilterAuctionsType;
use App\Form\Filters\FilterBidsType;
use App\Helper\RequestBodyHelper;
use App\Helper\ResponseHelper;
use App\Helper\SortHelper;
use App\Helper\StringHelper;
use App\Model\CancelBid;
use App\Repository\AuctionRepository;
use App\Repository\BidRepository;
use App\Service\FilterService;
use App\Service\ImmutableService;
use App\Service\SignatureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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
                    example: '4667245',
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
    public function create(
        Request           $request,
        BidRepository     $bidRepository,
        AuctionRepository $auctionRepository,
        ImmutableService  $immutableService
    ): Response
    {
        $bid = new Bid();
        $form = $this->createForm(AddBidType::class, $bid);

        $form->submit((array)json_decode((string)$request->getContent(), false));

        if ($form->isValid()) {
            $auction = $auctionRepository->findOneBy([
                'id' => $form->get('auctionId')->getData(),
                'status' => Auction::STATUS_ACTIVE,
            ]);

            if ($auction instanceof Auction) {
                $bid->setAuction($auction);
            } else {
                throw new NotFoundHttpException(
                    sprintf('Active auction with id %s not found', $form->get('auctionId')->getData())
                );
            }

            $bidExist = $bidRepository->findOneBy([
                'transferId' => StringHelper::sanitize((string)$bid->getTransferId())
            ]);

            if ($bidExist instanceof Bid) {
                throw new ConflictHttpException(sprintf('Bid with transfer %s already exists', $bid->getTransferId()));
            }

            $immutableService->checkBidDeposit($bid, $auction);
            $bidRepository->add($bid);
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

    #[Route('/{id}', name: 'api_bids_delete', methods: 'DELETE')]
    #[OA\Delete(
        operationId: Bid::GROUP_DELETE_BID,
        description: 'Cancel a bid',
        summary: 'Cancel a bid',
    )]
    #[OA\RequestBody(
        description: 'Bid to cancel',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'publicKey',
                    description: 'Public key of the bid\'s creator',
                ),
                new OA\Property(
                    property: 'signature',
                    description: 'Bid id signed by bid\'s creator',
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Bid.item'),
    )]
    public function cancel(
        string           $id,
        Request          $request,
        BidRepository    $bidRepository,
        SignatureService $signatureService
    ): Response
    {
        $bid = $bidRepository->findOneBy([
            'id' => $id,
            'status' => Auction::STATUS_ACTIVE,
        ]);

        if (!$bid instanceof Bid) {
            throw new NotFoundHttpException(sprintf('Active bid with id %s not found', $id));
        }

        $cancelBid = new CancelBid();

        $form = $this->createForm(CancelBidType::class, $cancelBid);
        $form->submit(RequestBodyHelper::map($request));

        if (!$form->isValid()) {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        if (!$signatureService->verifySignature(
            $id,
            $cancelBid->getPublicKey(),
            $cancelBid->getSignature(),
        )) {
            throw new BadRequestHttpException(sprintf(
                'Signature does not match with public key %s',
                $cancelBid->getPublicKey(),
            ));
        }

        if (strtolower($bid->getOwner()) !== strtolower($cancelBid->getPublicKey())) {
            throw new UnauthorizedHttpException('', sprintf('Address %s is not the owner of id %s',
                $cancelBid->getPublicKey(),
                $id,
            ));
        }

        $bid->setStatus(Auction::STATUS_CANCELLED);
        $bidRepository->add($bid);

        return $this->json($bid, Response::HTTP_OK, [], [
            'groups' => [
                Bid::GROUP_GET_BID,
                Bid::GROUP_GET_BID_WITH_AUCTION,
            ],
        ]);
    }
}
