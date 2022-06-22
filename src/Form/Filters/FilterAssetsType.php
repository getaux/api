<?php

declare(strict_types=1);

namespace App\Form\Filters;

use Symfony\Component\Form\FormBuilderInterface;

class FilterAssetsType extends AbstractFilterType
{
    public const ORDER_FIELDS = ['name', 'createdAt', 'internalId'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildPaginate($builder, self::ORDER_FIELDS);
    }
}
