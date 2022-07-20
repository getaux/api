<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Collection;
use App\Form\Filters\FilterCollectionsType;
use App\Helper\ResponseHelper;
use App\Helper\SortHelper;
use App\Repository\CollectionRepository;
use App\Service\CollectionService;
use App\Service\FilterService;
use App\Service\ImmutableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/collections')]
#[OA\Tag(name: 'Collections')]
class CollectionController extends AbstractController
{
    #[Route(name: 'api_collections_list', methods: 'GET')]
    #[OA\Get(
        operationId: Collection::GROUP_GET_COLLECTIONS,
        description: 'Get a list of collections',
        summary: 'Get a list of collections',
        parameters: [
            new OA\Parameter(
                name: 'orderBy',
                description: 'Property to sort by',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(enum: FilterCollectionsType::ORDER_FIELDS),
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
        ],
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Collection.item'),
                ),
                new OA\Property(
                    property: 'totalResults',
                    description: 'Total results of the query filtered',
                    type: 'integer',
                ),
            ],
        )
    )]
    public function list(
        Request              $request,
        FilterService        $filterService,
        CollectionRepository $collectionRepository,
        CollectionService    $collectionService
    ): Response {
        $form = $this->createForm(FilterCollectionsType::class);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        list($filters, $order, $limit, $offset) = $filterService->map((array)$form->getData());

        $totalCollections = $collectionRepository->customCount();
        $collections = $collectionRepository->customFindAll($order, $limit, $offset);

        $auctionsByCollection = $collectionRepository->findAuctionsByCollection();

        $collectionService->mapTotalAuctions($collections, $auctionsByCollection);

        return $this->json([
            'result' => $collections,
            'totalResults' => $totalCollections,
        ], Response::HTTP_OK, [], [
            'groups' => [
                Collection::GROUP_GET_COLLECTION,
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_collections_show', requirements: ['id' => '\d+'], methods: 'GET')]
    #[OA\Get(
        operationId: Collection::GROUP_GET_COLLECTION,
        description: 'Get details of a collection',
        summary: 'Get details of a collection',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Collection.item'),
    )]
    public function show(CollectionRepository $collectionRepository, int $id): Response
    {
        $collection = $collectionRepository->find($id);

        if (!$collection instanceof Collection) {
            throw new NotFoundHttpException(sprintf('Collection with id %s not found', $id));
        }

        $collection->setTotalAuctions(intval($collectionRepository->findAuctionsForOneCollection($collection)));

        return $this->json($collection, Response::HTTP_OK, [], [
            'groups' => [
                Collection::GROUP_GET_COLLECTION
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_collections_update', requirements: ['id' => '\d+'], methods: 'PUT')]
    #[OA\Put(
        operationId: Collection::GROUP_UPDATE_COLLECTION,
        description: 'Update metadata of a collection',
        summary: 'Update metadata of a collection',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Collection.item'),
    )]
    public function update(int $id, CollectionRepository $collectionRepository, ImmutableService $immutableService): Response
    {
        $collection = $collectionRepository->find($id);

        if (!$collection instanceof Collection) {
            throw new NotFoundHttpException(sprintf('Collection with id %s not found', $id));
        }

        $immutableService->updateCollection(
            $collection->getAddress(),
            $collection,
        );

        $collection->setTotalAuctions(intval($collectionRepository->findAuctionsForOneCollection($collection)));

        return $this->json($collection, Response::HTTP_OK, [], [
            'groups' => [
                Collection::GROUP_GET_COLLECTION,
            ],
        ]);
    }
}
