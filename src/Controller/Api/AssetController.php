<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Asset;
use App\Form\Filters\FilterAssetsType;
use App\Helper\ResponseHelper;
use App\Helper\SortHelper;
use App\Repository\AssetRepository;
use App\Service\FilterService;
use App\Service\ImmutableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/assets')]
#[OA\Tag(name: 'Assets')]
class AssetController extends AbstractController
{
    #[Route(name: 'api_assets_list', methods: 'GET')]
    #[OA\Get(
        operationId: Asset::GROUP_GET_ASSETS,
        description: 'Get a list of assets',
        summary: 'Get a list of assets',
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
            )
            , new OA\Parameter(
                name: 'order_by',
                description: 'Property to sort by',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(enum: FilterAssetsType::ORDER_FIELDS),
                ],
            ),
            new OA\Parameter(name: 'direction',
                description: 'Direction to sort (asc/desc)',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'direction', enum: SortHelper::WAYS),
                ],
            ),
            new OA\Parameter(name: 'collection',
                description: 'Collection contract address',
                in: 'query',
                required: false,
                examples: [
                    new OA\Schema(title: 'collection', type: 'string'),
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
                    items: new OA\Items(ref: '#/components/schemas/Asset.item'),
                ),
                new OA\Property(
                    property: 'totalResults',
                    description: 'Total results of the query filtered',
                    type: 'integer',
                ),
            ],
        )
    )]
    public function list(Request $request, FilterService $filterService, AssetRepository $assetRepository): Response
    {
        $form = $this->createForm(FilterAssetsType::class);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            throw new BadRequestException(ResponseHelper::getFirstError((string)$form->getErrors(true)));
        }

        list($filters, $order, $limit, $offset) = $filterService->map((array)$form->getData());

        $totalAssets = $assetRepository->customCount($filters);
        $assets = $assetRepository->customFindAll($filters, $order, $limit, $offset);

        return $this->json([
            'result' => $assets,
            'totalResults' => $totalAssets,
        ], Response::HTTP_OK, [], [
            'groups' => [
                Asset::GROUP_GET_ASSET,
                Asset::GROUP_GET_ASSET_WITH_AUCTIONS,
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_assets_show', requirements: ['id' => '\d+'], methods: 'GET')]
    #[OA\Get(
        operationId: Asset::GROUP_GET_ASSET,
        description: 'Get details of an asset',
        summary: 'Get details of an asset',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Asset.item'),
    )]
    public function show(AssetRepository $assetRepository, int $id): Response
    {
        $asset = $assetRepository->find($id);

        if (!$asset) {
            throw new NotFoundHttpException(sprintf('Asset with id %s not found', $id));
        }

        return $this->json($asset, Response::HTTP_OK, [], [
            'groups' => [
                Asset::GROUP_GET_ASSET,
                Asset::GROUP_GET_ASSET_WITH_AUCTIONS,
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_assets_update', requirements: ['id' => '\d+'], methods: 'PUT')]
    #[OA\Put(
        operationId: Asset::GROUP_UPDATE_ASSET,
        description: 'Update metadata of an asset',
        summary: 'Update metadata of an asset',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Asset.item'),
    )]
    public function update(int $id, AssetRepository $assetRepository, ImmutableService $immutableService): Response
    {
        $asset = $assetRepository->find($id);

        if (!$asset instanceof Asset) {
            throw new NotFoundHttpException(sprintf('Asset with id %s not found', $id));
        }

        $immutableService->updateAsset(
            $asset->getTokenAddress(),
            $asset->getInternalId(),
            $asset,
        );

        return $this->json($asset, Response::HTTP_OK, [], [
            'groups' => [
                Asset::GROUP_GET_ASSET,
                Asset::GROUP_GET_ASSET_WITH_AUCTIONS,
            ],
        ]);
    }
}
