<?php

declare(strict_types=1);

namespace App\Repository;

interface FilterableRepositoryInterface
{
    public function customCount(array $filters): mixed;

    public function customFindAll(array $filters, array $order, int $limit, ?int $offset): array;
}
