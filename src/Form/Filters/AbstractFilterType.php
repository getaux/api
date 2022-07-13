<?php

declare(strict_types=1);

namespace App\Form\Filters;

use App\Helper\SortHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Range;

abstract class AbstractFilterType extends AbstractType
{
    public function buildPaginate(FormBuilderInterface $builder, array $orderFields): void
    {
        $builder->add('pageSize', IntegerType::class, [
            'constraints' => [
                new Range([
                    'min' => 1,
                    'max' => 100,
                    'notInRangeMessage' => 'Invalid parameter: pageSize field must be between 1 to 100',
                ]),
            ],
            'invalid_message' => 'Invalid parameter: pageSize field must be between 1 to 100',
        ])->add('page', IntegerType::class, [
            'required' => false,
            'constraints' => [
                new Range([
                    'min' => 1,
                    'notInRangeMessage' => 'Invalid parameter: page field must be between 1 to 100',
                ]),
            ],
            'invalid_message' => 'Invalid parameter: page field must be between 1 to 100',
        ])->add('orderBy', TextType::class, [
            'constraints' => [
                new Choice([], $orderFields, null, null, null, null, null, 'Invalid parameter: orderBy field is invalid'),
            ]
        ])->add('direction', TextType::class, [
            'constraints' => [
                new Choice([], SortHelper::WAYS, null, null, null, null, null, 'Invalid parameter: direction field is invalid'),
            ],
        ])->add('collection', TextType::class);
    }
}
