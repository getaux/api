<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Auction;
use App\Helper\TokenHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class AddAuctionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', TextType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: type field should not be blank'),
                new Choice([], Auction::TYPES, null, null, null, null, null, 'Invalid parameter: type field is invalid'),
            ]
        ])->add('transferId', TextType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: transferId field should not be blank'),
            ]
        ])->add('quantity', TextType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: quantity field should not be blank'),
                new Positive([], 'Invalid parameter: quantity should be positive'),
            ]
        ])->add('decimals', IntegerType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: decimals field should not be blank'),
                new Positive([], 'Invalid parameter: decimals should be positive'),
            ]
        ])->add('tokenType', TextType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: tokenType field should not be blank'),
                new Choice([], TokenHelper::TOKENS, null, null, null, null, null, 'Invalid parameter: tokenType field is invalid'),
            ]
        ])->add('endAt', DateTimeType::class, [
            'widget' => 'single_text',
            'html5' => false,
            'constraints' => [
                new NotBlank([], 'Missing parameter: endAt field should not be blank'),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Auction::class,
        ]);
    }
}
