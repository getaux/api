<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Bid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class AddBidType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('transferId', TextType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: transferId field should not be blank'),
            ],
        ])->add('auctionId', IntegerType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: decimals field should not be blank'),
                new Positive([], 'Invalid parameter: decimals should be positive'),
            ],
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bid::class,
        ]);
    }
}
