<?php

declare(strict_types=1);

namespace App\Service;

class FilterService
{
    public function map(array $parameters): array
    {
        $filters = [
            'filters' => [],
            'orderBy' => 'id',
            'orderDirection' => 'desc',
            'pageSize' => 20,
            'page' => 0,
        ];

        foreach ($parameters as $field => $value) {
            if ($value !== null) {
                switch ($field) {
                    case 'page':
                        $filters['page'] = $value;
                        break;
                    case 'pageSize':
                        $filters['pageSize'] = $value;
                        break;
                    case 'orderBy':
                        $filters['orderBy'] = $value;
                        break;
                    case 'direction':
                        $filters['orderDirection'] = $value;
                        break;
                    default:
                        $filters['filters'][$field] = $value;
                }
            }
        }

        return [
            $filters['filters'],
            [
                $filters['orderBy'] => $filters['orderDirection'],
            ],
            $filters['pageSize'],
            $filters['page'] ? $filters['pageSize'] * ($filters['page'] - 1) : null,
        ];
    }
}
