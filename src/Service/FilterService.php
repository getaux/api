<?php

declare(strict_types=1);

namespace App\Service;

class FilterService
{
    public function map(array $parameters): array
    {
        $filters = [
            'filters' => [],
            'order_by' => 'id',
            'order_direction' => 'asc',
            'page_size' => 20,
            'page' => 0,
        ];

        foreach ($parameters as $field => $value) {
            if ($value !== null) {
                switch ($field) {
                    case 'page':
                        $filters['page'] = $value;
                        break;
                    case 'page_size':
                        $filters['page_size'] = $value;
                        break;
                    case 'order_by':
                        $filters['order_by'] = $value;
                        break;
                    case 'direction':
                        $filters['order_direction'] = $value;
                        break;
                    default:
                        $filters['filters'][$field] = $value;
                }
            }
        }

        return [
            $filters['filters'],
            [
                $filters['order_by'] => $filters['order_direction']
            ],
            $filters['page_size'],
            $filters['page'] ? ($filters['page_size'] * $filters['page']) - 1 : [],
        ];
    }
}