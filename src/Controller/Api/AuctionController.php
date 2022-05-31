<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Auction;
use App\Form\AddAuctionType;
use App\Form\CancelAuctionType;
use App\Form\Filters\FilterAuctionsType;
use App\Helper\RequestBodyHelper;
use App\Helper\ResponseHelper;
use App\Helper\SortHelper;
use App\Helper\StringHelper;
use App\Helper\TokenHelper;
use App\Model\CancelAuction;
use App\Repository\AuctionRepository;
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

#[Route('/auctions')]
#[OA\Tag(name: 'Auctions')]
class AuctionController extends AbstractController
{
    #[Route(name: 'api_auctions_list', methods: 'GET')]
    #[OA\Get(
        operationId: Auction::GROUP_GET_AUCTIONS,
        description: 'Get a list of auctions',
        summary: 'Get a list of auctions',
        parameters: [
            new OA\Parameter(
                name: 'page_size',
                description: 'Page size of the result',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page of the result (for paginate)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
            , new OA\Parameter(
                name: 'order_by',
                description: 'Property to sort by',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(enum: FilterAuctionsType::ORDER_FIELDS),
                ]
            ),
            new OA\Parameter(name: 'direction',
                description: 'Direction to sort (asc/desc)',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'direction', enum: SortHelper::WAYS),
                ]
            ),
            new OA\Parameter(name: 'type',
                description: 'Type of these auctions',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'type', enum: Auction::TYPES),
                ]
            ),
            new OA\Parameter(name: 'status',
                description: 'Status of these auctions',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'status', enum: Auction::STATUS),
                ]
            ),
            new OA\Parameter(name: 'tokenType',
                description: 'Token type of the asset these auctions',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'tokenType', enum: TokenHelper::TOKENS),
                ]
            ),
            new OA\Parameter(name: 'collection',
                description: 'Collection contract address',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'collection', type: 'string'),
                ]
            )
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Auction.item')
                ),
                new OA\Property(
                    property: 'totalResults',
                    description: 'Total results of the query filtered',
                    type: 'integer'
                )
            ],
        )
    )]
    public function list(Request $request, FilterService $filterService, AuctionRepository $auctionRepository): Response
    {
        $form = $this->createForm(FilterAuctionsType::class);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        list($filters, $order, $limit, $offset) = $filterService->map((array)$form->getData());

        $totalAuctions = $auctionRepository->customCount($filters);
        $auctions = $auctionRepository->customFindAll($filters, $order, $limit, $offset);

        return $this->json([
            'result' => $auctions,
            'totalResults' => $totalAuctions,
        ], Response::HTTP_OK, [], [
            'groups' => [Auction::GROUP_GET_AUCTION, Auction::GROUP_GET_AUCTION_WITH_ASSET]
        ]);
    }

    #[Route('/{id}', name: 'api_auctions_show', methods: 'GET')]
    #[OA\Get(
        operationId: Auction::GROUP_GET_AUCTION,
        description: 'Get details of an auction',
        summary: 'Get details of an auction'
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Auction.item')
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

    #[Route(name: 'api_auctions_create', methods: 'POST')]
    #[OA\Post(
        operationId: Auction::GROUP_POST_AUCTION,
        description: 'Create an auction',
        summary: 'Create an auction'
    )]
    #[OA\RequestBody(
        description: 'Auction to create',
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/Auction.post',
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Created',
        content: new OA\JsonContent(ref: '#/components/schemas/Auction.item')
    )]
    public function create(
        Request           $request,
        AuctionRepository $auctionRepository,
        ImmutableService  $immutableService
    ): Response
    {
        $auction = new Auction();
        $form = $this->createForm(AddAuctionType::class, $auction);

        $form->submit(RequestBodyHelper::map($request));

        if (!$form->isValid()) {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        $auctionExist = $auctionRepository->findOneBy([
            'transferId' => StringHelper::sanitize((string)$auction->getTransferId())
        ]);

        if ($auctionExist instanceof Auction) {
            throw new ConflictHttpException('Auction already exists');
        }

        $immutableService->checkDeposit($auction);
        $auctionRepository->add($auction);

        return $this->json($auction, Response::HTTP_OK, [], [
            'groups' => [Auction::GROUP_GET_AUCTION, Auction::GROUP_GET_AUCTION_WITH_ASSET]
        ]);
    }

    #[Route('/{id}', name: 'api_auctions_delete', methods: 'DELETE')]
    #[OA\Delete(
        operationId: Auction::GROUP_DELETE_AUCTION,
        description: 'Cancel an auction',
        summary: 'Cancel an auction'
    )]
    #[OA\RequestBody(
        description: 'Auction to cancel',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'publicKey',
                    description: 'Public key of the auction\'s creator',
                ),
                new OA\Property(
                    property: 'signature',
                    description: 'Auction id signed by auction\'s creator',
                ),
            ],
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Auction.item')
    )]
    public function cancel(
        string            $id,
        Request           $request,
        AuctionRepository $auctionRepository,
        SignatureService  $signatureService
    ): Response
    {
        $auction = $auctionRepository->findOneBy([
            'id' => $id,
            'status' => Auction::STATUS_ACTIVE,
        ]);

        if (!$auction) {
            throw new NotFoundHttpException(sprintf('Active auction with id %s not found', $id));
        }

        $cancelAuction = new CancelAuction();

        $form = $this->createForm(CancelAuctionType::class, $cancelAuction);
        $form->submit(RequestBodyHelper::map($request));

        if (!$form->isValid()) {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        if (!$signatureService->verifySignature(
            $id,
            $cancelAuction->getPublicKey(),
            $cancelAuction->getSignature(),
        )) {
            throw new BadRequestHttpException(sprintf(
                'Signature mismatch with public key %s',
                $cancelAuction->getPublicKey()
            ));
        }

        if ($auction->getOwner() !== $cancelAuction->getPublicKey()) {
            throw new UnauthorizedHttpException('', sprintf('Address %s is not the owner of auction %s',
                $cancelAuction->getPublicKey(),
                $id,
            ));
        }

        $auction->setStatus(Auction::STATUS_CANCELLED);
        $auctionRepository->add($auction);

        return $this->json($auction, Response::HTTP_OK, [], [
            'groups' => [Auction::GROUP_GET_AUCTION, Auction::GROUP_GET_AUCTION_WITH_ASSET]
        ]);
    }
}
