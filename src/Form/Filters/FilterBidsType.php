<?php

declare(strict_types=1);

namespace App\Form\Filters;

use App\Entity\Bid;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Positive;

class FilterBidsType extends AbstractFilterType
{
    const ORDER_FIELDS = ['createdAt', 'quantity'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildPaginate($builder, self::ORDER_FIELDS);

        $builder->add('auction_id', IntegerType::class, [
            'required' => false,
            'constraints' => [
                new Positive([], 'Invalid parameter: auction_id should be positive'),
            ],
        ])->add('status', TextType::class, [
            'constraints' => [
                new Choice([], Bid::STATUS, null, null, null, null, null, 'Invalid parameter: status field is invalid'),
            ],
        ]);
    }
}
