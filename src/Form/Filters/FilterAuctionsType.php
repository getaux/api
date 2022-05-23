<?php

declare(strict_types=1);

namespace App\Form\Filters;

use App\Entity\Auction;
use App\Helper\SortHelper;
use App\Helper\TokenHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Range;

class FilterAuctionsType extends AbstractType
{
    const ORDER_FIELDS = ['createdAt', 'endAt', 'quantity'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('page_size', IntegerType::class, [
            'constraints' => [
                new Range([
                    'min' => 1,
                    'max' => 100,
                    'notInRangeMessage' => 'Invalid parameter: page_size field must be between 1 to 100',
                ]),
            ],
            'invalid_message' => 'Invalid parameter: page_size field must be between 1 to 100',
        ])->add('page', IntegerType::class, [
            'required' => false,
            'constraints' => [
                new Range([
                    'min' => 1,
                    'notInRangeMessage' => 'Invalid parameter: page_size field must be between 1 to 100',
                ]),
            ],
            'invalid_message' => 'Invalid parameter: page_size field must be between 1 to 100',
        ])->add('order_by', TextType::class, [
            'constraints' => [
                new Choice([], self::ORDER_FIELDS, null, null, null, null, null, 'Invalid parameter: order_by field is invalid'),
            ]
        ])->add('direction', TextType::class, [
            'constraints' => [
                new Choice([], SortHelper::WAYS, null, null, null, null, null, 'Invalid parameter: direction field is invalid'),
            ],
        ])->add('type', TextType::class, [
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
        ])->add('collection', TextType::class);
    }
}
