<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Asset;
use App\Repository\AssetRepository;
use App\Service\ImmutableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        summary: 'Get a list of assets'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'result',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Asset.list')
                )
            ],
        )
    )]
    public function list(AssetRepository $assetRepository): Response
    {
        /** @todo refactor with parameters */
        $assets = $assetRepository->findAll();

        return $this->json([
            'result' => $assets,
        ], Response::HTTP_OK, [], [
            'groups' => [Asset::GROUP_GET_ASSET, Asset::GROUP_GET_ASSET_WITH_AUCTIONS],
        ]);
    }

    #[Route('/{id}', name: 'api_assets_show', methods: 'GET')]
    #[OA\Get(
        operationId: Asset::GROUP_GET_ASSET,
        description: 'Get details of an asset',
        summary: 'Get details of an asset'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Asset.list')
    )]
    public function show(AssetRepository $assetRepository, string $id): Response
    {
        $asset = $assetRepository->find((int)$id);

        if (!$asset) {
            throw new NotFoundHttpException(sprintf('Asset with id %s not found', $id));
        }

        return $this->json($asset, Response::HTTP_OK, [], [
            'groups' => Asset::GROUP_GET_ASSET_WITH_AUCTIONS
        ]);
    }

    #[Route('/{id}', name: 'api_assets_update', methods: 'PUT')]
    #[OA\Put(
        operationId: Asset::GROUP_UPDATE_ASSET,
        description: 'Update metadata of an asset',
        summary: 'Update metadata of an asset'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(ref: '#/components/schemas/Asset.item')
    )]
    public function update(string $id, AssetRepository $assetRepository, ImmutableService $immutableService): Response
    {
        $asset = $assetRepository->find((int)$id);

        if (!$asset instanceof Asset) {
            throw new NotFoundHttpException(sprintf('Asset with id %s not found', $id));
        }

        $immutableService->updateAsset(
            (string)$asset->getTokenAddress(),
            (string)$asset->getInternalId(),
            $asset
        );

        return $this->json($asset, Response::HTTP_OK, [], [
            'groups' => Asset::GROUP_GET_ASSET_WITH_AUCTIONS
        ]);
    }
}
