<?php

declare(strict_types=1);

namespace App\Form\Filters;

use App\Entity\Auction;
use App\Helper\TokenHelper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;

class FilterAuctionsType extends AbstractFilterType
{
    const ORDER_FIELDS = ['createdAt', 'endAt', 'quantity'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildPaginate($builder, self::ORDER_FIELDS);

        $builder->add('type', TextType::class, [
            'constraints' => [
                new Choice([], Auction::TYPES, null, null, null, null, null, 'Invalid parameter: type field is invalid'),
            ],
        ])->add('status', TextType::class, [
            'constraints' => [
                new Choice([], Auction::STATUS, null, null, null, null, null, 'Invalid parameter: status field is invalid'),
            ],
        ])->add('tokenType', TextType::class, [
            'constraints' => [
                new Choice([], TokenHelper::TOKENS, null, null, null, null, null, 'Invalid parameter: tokenType field is invalid'),
            ],
        ])->add('owner', TextType::class);
    }
}
