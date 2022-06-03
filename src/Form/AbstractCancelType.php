<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class AbstractCancelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('publicKey', TextType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: publicKey field should not be blank'),
            ]
        ])->add('signature', TextType::class, [
            'constraints' => [
                new NotBlank([], 'Missing parameter: signature field should not be blank'),
            ]
        ]);
    }
}
