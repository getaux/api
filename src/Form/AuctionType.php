<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Auction;
use App\Helper\TokenHelper;
use DateTimeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuctionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', TextType::class, [
            'constraints' => [
                new NotBlank([], 'type should not be blank'),
            ]
        ])->add('transferId', TextType::class, [
            'constraints' => [
                new NotBlank([], 'transferId should not be blank'),
            ]
        ])->add('quantity', TextType::class, [
            'constraints' => [
                new NotBlank([], 'transferId should not be blank'),
            ]
        ])->add('decimals', TextType::class, [
            'constraints' => [
                new NotBlank([], 'decimals should not be blank'),
            ]
        ])->add('tokenType', TextType::class, [
            'constraints' => [
                new NotBlank([], 'tokenType should not be blank'),
                new Choice([], TokenHelper::TOKENS, null, null, null, null, null, 'tokenType is invalid'),
            ]
        ])->add('endAt', DateTime::class, [
            'format' => DateTimeInterface::ISO8601
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Auction::class,
        ]);
    }
}
